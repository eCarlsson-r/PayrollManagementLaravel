"""Agent 3 — Compliance Review.

Validates each row against the regional minimum wage (UMR) and flags anomalies
via POST /api/payroll/flag, then hands off to the Report Generator. Flagged
rows are still forwarded (humans resolve flags); they are not dropped.

Uses an LLM (AI/ML API) only to phrase a human-readable explanation for the
flags. The flag decision itself is the deterministic UMR rule — the LLM never
decides who gets flagged.
"""

import asyncio
import os

from common.llm import LLM
from common.payroll_api import PayrollClient
from common.pipeline import (
    BasePayrollAdapter,
    COMPLIANCE_REVIEWER,
    REPORT_GENERATOR,
    run_agent,
)

# Default: Jakarta UMR 2024 (~Rp 4.9M). Override with UMR_MINIMUM in .env.
UMR_MINIMUM = float(os.getenv("UMR_MINIMUM", "4900000"))


class ComplianceReviewAgent(BasePayrollAdapter):
    name = COMPLIANCE_REVIEWER
    next_agent = REPORT_GENERATOR

    def __init__(self):
        super().__init__()
        self.client = PayrollClient()
        self.llm = LLM("aimlapi")

    async def _explain(self, flags: list[dict]) -> str | None:
        """LLM-phrased summary of the flags; None if LLM unavailable."""
        if not flags:
            return None
        listing = "\n".join(
            f"- employee {f['employee_id']}: gross Rp {f['gross_amount']:,.0f}" for f in flags
        )
        return await self.llm.complete(
            system=(
                "You are a payroll compliance officer in Indonesia. Briefly and "
                "professionally explain why the listed employees were flagged for "
                "being paid below the regional minimum wage (UMR), and recommend "
                "the corrective action. Be concise."
            ),
            user=f"UMR minimum: Rp {UMR_MINIMUM:,.0f}\nFlagged rows:\n{listing}",
        )

    async def handle(self, msg, tools) -> None:
        payload = self.extract_json(msg.content)
        if not payload or "payments" not in payload:
            return

        period = payload.get("period")
        flags = []
        for row in payload["payments"]:
            gross = float(row.get("gross", row.get("amount", 0)))
            net = float(row.get("net", gross))

            # MONTHLY/COMMISSION gross below UMR is the primary anomaly.
            if gross and gross < UMR_MINIMUM:
                flag = {
                    "employee_id": row.get("employee_id"),
                    "period": period,
                    "reason": f"Gross pay Rp {gross:,.0f} is below UMR minimum Rp {UMR_MINIMUM:,.0f}",
                    "severity": "critical",
                    "gross_amount": gross,
                    "net_amount": net,
                    "data": {"umr": UMR_MINIMUM},
                }
                flags.append(flag)
                row["compliance"] = "flagged"
            else:
                row["compliance"] = "ok"

        explanation = None
        if flags:
            await tools.send_event(
                f"Flagging {len(flags)} row(s) for human review", message_type="thought"
            )
            await self.client.flag(flags)
            explanation = await self._explain(flags)
            if explanation:
                # Attach to each flag record and surface it in the room.
                for f in flags:
                    f.setdefault("data", {})["explanation"] = explanation
                await tools.send_event(explanation, message_type="thought")

        payload["stage"] = "reviewed"
        payload["flags"] = flags
        if explanation:
            payload["compliance_note"] = explanation
        await self.handoff(
            tools,
            payload,
            summary=f"Reviewed {len(payload['payments'])} row(s); {len(flags)} flagged.",
        )


if __name__ == "__main__":
    asyncio.run(run_agent("BAND_AGENT3", ComplianceReviewAgent()))
