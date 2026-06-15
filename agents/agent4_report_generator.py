"""Agent 4 — Report Generator (terminal stage) with human-in-the-loop gate.

Flow:
  1. Submit non-flagged entries immediately (POST /api/payroll/submit).
  2. If entries were flagged by Agent 3, WAIT for a human to approve/reject them
     on the /workflow page — poll GET /api/payroll/flags until each flagged
     employee's decision is no longer "pending".
  3. Submit approved entries, skip rejected (and timed-out) ones.
  4. Post a payslip report with a Featherless-generated manager narrative.
"""

import asyncio
import time

from common.llm import LLM
from common.pipeline import (
    BasePayrollAdapter,
    REPORT_GENERATOR,
    run_agent,
)

# How long Agent 4 will wait for human decisions before giving up.
APPROVAL_POLL_INTERVAL = 3.0   # seconds between polls
APPROVAL_TIMEOUT = 600.0       # 10 minutes


class ReportGeneratorAgent(BasePayrollAdapter):
    name = REPORT_GENERATOR
    next_agent = None  # terminal

    def __init__(self):
        super().__init__()  # base provides self.client
        self.llm = LLM("featherless")

    async def handle(self, msg, tools) -> None:
        payload = self.extract_json(msg.content)
        if not payload or "payments" not in payload:
            return

        rows = payload["payments"]
        date = payload.get("date")
        period = payload.get("period")

        flagged = [r for r in rows if r.get("compliance") == "flagged"]
        ok = [r for r in rows if r.get("compliance") != "flagged"]

        # 1) Submit non-flagged entries straight away.
        submitted = []
        if ok:
            await self.client.submit([self._submission(r, date) for r in ok])
            submitted += ok

        # 2) Gate: wait for human approve/reject on the flagged entries.
        approved, rejected = [], []
        if flagged:
            await tools.send_event(
                f"{len(flagged)} flagged entr(ies) awaiting human approval — review at /workflow",
                message_type="task",
            )
            await self._log(
                "⏸ Awaiting human approval for "
                + ", ".join(f"employee {r.get('employee_id')}" for r in flagged),
                period=period,
            )

            decisions = await self._await_decisions(period, flagged)
            for r in flagged:
                info = decisions.get(str(r.get("employee_id")), {})
                if info.get("decision") == "approved":
                    # Carry corrected_amount into the row so _submission and _render can use it.
                    if info.get("corrected_amount"):
                        r = {**r, "_corrected_amount": int(info["corrected_amount"])}
                    approved.append(r)
                else:
                    rejected.append(r)  # rejected or timed-out -> not paid

            if approved:
                await self.client.submit([self._submission(r, date) for r in approved])
                submitted += approved

        # 3) Final report (deterministic figures + optional LLM narrative).
        report = self._render(rows, payload, submitted, approved, rejected)
        narrative = await self.llm.complete(
            system=(
                "You are an HR payroll assistant. Given a payroll summary, write a "
                "short, friendly manager-facing note (2-3 sentences) highlighting "
                "total payout, headcount, and any flagged/rejected employees. CRITICAL: "
                "do not compute or invent any numbers. Use figures only as they appear "
                "verbatim in the summary — quote the 'Total net payout' line exactly as "
                "given; never add up amounts yourself."
            ),
            user=report,
        )
        if narrative:
            report = f"{narrative}\n\n{report}"

        await self.finish(tools, report, period=period)

    async def _await_decisions(self, period, flagged) -> dict[str, dict]:
        """Poll Laravel until every flagged employee has a non-pending decision
        (or the timeout elapses). Returns {employee_id: {decision, corrected_amount}}."""
        pending = {str(r.get("employee_id")) for r in flagged}
        decisions: dict[str, dict] = {}
        deadline = time.monotonic() + APPROVAL_TIMEOUT
        while pending and time.monotonic() < deadline:
            try:
                resp = await self.client.list_flags(period)
            except Exception:
                resp = {"flags": []}
            for f in resp.get("flags", []):
                eid = str(f.get("employee_id"))
                if eid in pending and f.get("decision") in ("approved", "rejected"):
                    decisions[eid] = {
                        "decision": f["decision"],
                        "corrected_amount": f.get("corrected_amount"),
                    }
                    pending.discard(eid)
            if pending:
                await asyncio.sleep(APPROVAL_POLL_INTERVAL)
        return decisions

    @staticmethod
    def _submission(r, date) -> dict:
        amount = r.get("_corrected_amount") or float(r.get("net", r.get("amount", 0)))
        return {
            "employee_id": r.get("employee_id"),
            "amount": int(round(float(amount))),
            "date": date,
        }

    @staticmethod
    def _render(rows, payload, submitted, approved, rejected) -> str:
        approved_ids = {str(r.get("employee_id")) for r in approved}
        rejected_ids = {str(r.get("employee_id")) for r in rejected}
        # _corrected_amount lives on approved rows, not on the original rows list.
        corrected_map = {
            str(r.get("employee_id")): r["_corrected_amount"]
            for r in approved
            if r.get("_corrected_amount")
        }

        lines = [f"📄 Payroll report — {payload.get('period', payload.get('date'))}", ""]
        total_net = 0.0
        for r in rows:
            eid = str(r.get("employee_id"))
            gross = float(r.get("gross", r.get("amount", 0)))
            net = float(r.get("net", gross))
            corrected = corrected_map.get(eid)
            paid_net = float(corrected) if corrected else net
            tax = float(r.get("pph21", 0))
            bpjs = float(r.get("bpjs_total", 0))
            if eid in rejected_ids:
                tag = " 🚫 REJECTED (not paid)"
            elif eid in approved_ids:
                tag = f" ✅ APPROVED (corrected: Rp {paid_net:,.0f})" if corrected else " ✅ APPROVED"
            else:
                tag = ""
            if eid not in rejected_ids:
                total_net += paid_net
            lines.append(
                f"• Employee {r.get('employee_id')}: gross Rp {gross:,.0f} "
                f"− tax Rp {tax:,.0f} − BPJS Rp {bpjs:,.0f} = net Rp {net:,.0f}{tag}"
            )

        flagged_count = len(approved) + len(rejected)
        lines += [
            "",
            f"Total net payout: Rp {total_net:,.0f} across {len(submitted)} paid employee(s).",
            f"Flagged: {flagged_count} (approved {len(approved)}, rejected {len(rejected)}).",
        ]
        return "\n".join(lines)


if __name__ == "__main__":
    asyncio.run(run_agent("BAND_AGENT4", ReportGeneratorAgent()))
