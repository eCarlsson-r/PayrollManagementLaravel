"""Agent 2 — Payroll Calculator.

Receives the collected batch, applies Indonesian PPh 21 progressive tax and
BPJS employee deductions to each row (gross -> net), then hands off to the
Compliance Reviewer.
"""

import asyncio

from common.pipeline import (
    BasePayrollAdapter,
    PAYROLL_CALCULATOR,
    COMPLIANCE_REVIEWER,
    run_agent,
)
from common.tax import compute_deductions


class PayrollCalculatorAgent(BasePayrollAdapter):
    name = PAYROLL_CALCULATOR
    next_agent = COMPLIANCE_REVIEWER

    async def handle(self, msg, tools) -> None:
        payload = self.extract_json(msg.content)
        if not payload or "payments" not in payload:
            return  # not a batch addressed to this stage

        for row in payload["payments"]:
            gross = float(row.get("amount", 0))
            d = compute_deductions(gross)
            row["gross"] = d.gross
            row["pph21"] = d.pph21_monthly
            row["bpjs"] = d.bpjs
            row["bpjs_total"] = d.bpjs_total
            row["total_deductions"] = d.total_deductions
            row["net"] = d.net

        payload["stage"] = "taxed"
        await self.handoff(
            tools,
            payload,
            summary=f"Applied PPh 21 + BPJS to {len(payload['payments'])} row(s).",
        )


if __name__ == "__main__":
    asyncio.run(run_agent("BAND_AGENT2", PayrollCalculatorAgent()))
