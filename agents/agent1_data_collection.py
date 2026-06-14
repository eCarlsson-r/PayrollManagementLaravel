"""Agent 1 — Data Collection.

Trigger: a human (or orchestrator) @mentions this agent, optionally naming a
period, e.g. "@Data Collector run payroll for 2026-06".

Action: calls GET /api/payroll/calculate?date=<month-end> so the full
MONTHLY/COMMISSION set is returned (HOURLY-only off month-end), then hands the
batch to the Payroll Calculator.
"""

import asyncio

from common.payroll_api import PayrollClient
from common.pipeline import (
    BasePayrollAdapter,
    DATA_COLLECTOR,
    PAYROLL_CALCULATOR,
    month_end,
    parse_period,
    run_agent,
)


class DataCollectionAgent(BasePayrollAdapter):
    name = DATA_COLLECTOR
    next_agent = PAYROLL_CALCULATOR

    def __init__(self):
        super().__init__()
        self.client = PayrollClient()

    async def handle(self, msg, tools) -> None:
        period = parse_period(msg.content)
        date = month_end(period)  # always resolve to month-end

        await tools.send_event(f"Fetching payroll for {date}", message_type="thought")
        result = await self.client.calculate(date)
        payments = result.get("payments", [])

        payload = {
            "stage": "collected",
            "period": period or date[:7],
            "date": date,
            "payments": payments,
        }
        await self.handoff(
            tools,
            payload,
            summary=f"Collected {len(payments)} payroll row(s) for {date}.",
        )


if __name__ == "__main__":
    asyncio.run(run_agent("BAND_AGENT1", DataCollectionAgent()))
