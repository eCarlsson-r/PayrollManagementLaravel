"""Agent 4 — Report Generator (terminal stage).

Persists finalized net pay via POST /api/payroll/submit, builds per-employee
payslip lines, and posts a summary back to the room. An LLM (Featherless) adds
a natural-language narrative on top of the deterministic figures; if the LLM is
unavailable the deterministic table is posted on its own.
"""

import asyncio

from common.llm import LLM
from common.payroll_api import PayrollClient
from common.pipeline import (
    BasePayrollAdapter,
    REPORT_GENERATOR,
    run_agent,
)


class ReportGeneratorAgent(BasePayrollAdapter):
    name = REPORT_GENERATOR
    next_agent = None  # terminal

    def __init__(self):
        super().__init__()
        self.client = PayrollClient()
        self.llm = LLM("featherless")

    async def handle(self, msg, tools) -> None:
        payload = self.extract_json(msg.content)
        if not payload or "payments" not in payload:
            return

        rows = payload["payments"]
        date = payload.get("date")

        # Submit finalized net pay (integer rupiah) as Payment records.
        submissions = [
            {
                "employee_id": r.get("employee_id"),
                "amount": int(round(float(r.get("net", r.get("amount", 0))))),
                "date": date,
            }
            for r in rows
        ]
        result = await self.client.submit(submissions)

        report = self._render(rows, payload, result)

        # Optional LLM narrative on top of the deterministic figures.
        narrative = await self.llm.complete(
            system=(
                "You are an HR payroll assistant. Given a payroll summary, write a "
                "short, friendly manager-facing note (2-3 sentences) highlighting "
                "total payout, headcount, and any flagged employees. CRITICAL: do "
                "not compute or invent any numbers. Use figures only as they appear "
                "verbatim in the summary — quote the 'Total net payout' line exactly "
                "as given; never add up amounts yourself."
            ),
            user=report,
        )
        if narrative:
            report = f"{narrative}\n\n{report}"

        await self.finish(tools, report)

    @staticmethod
    def _render(rows, payload, submit_result) -> str:
        lines = [f"📄 Payroll report — {payload.get('period', payload.get('date'))}", ""]
        total_net = 0.0
        for r in rows:
            gross = float(r.get("gross", r.get("amount", 0)))
            net = float(r.get("net", gross))
            tax = float(r.get("pph21", 0))
            bpjs = float(r.get("bpjs_total", 0))
            total_net += net
            mark = " ⚠️ FLAGGED" if r.get("compliance") == "flagged" else ""
            lines.append(
                f"• Employee {r.get('employee_id')}: gross Rp {gross:,.0f} "
                f"− tax Rp {tax:,.0f} − BPJS Rp {bpjs:,.0f} = net Rp {net:,.0f}{mark}"
            )
        flags = payload.get("flags", [])
        lines += [
            "",
            f"Total net payout: Rp {total_net:,.0f} across {len(rows)} employee(s).",
            f"Submitted: {submit_result.get('created_count', 0)} payment(s); "
            f"flags raised: {len(flags)}.",
        ]
        return "\n".join(lines)


if __name__ == "__main__":
    asyncio.run(run_agent("BAND_AGENT4", ReportGeneratorAgent()))
