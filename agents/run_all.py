import asyncio
import traceback

from agent1_data_collection import DataCollectionAgent
from agent2_payroll_calculator import PayrollCalculatorAgent
from agent3_compliance_review import ComplianceReviewAgent
from agent4_report_generator import ReportGeneratorAgent
from common.pipeline import run_agent

RECONNECT_DELAY = 5  # seconds between reconnect attempts


async def run_forever(prefix: str, adapter) -> None:
    """Keep the agent alive, reconnecting automatically on any disconnect or error."""
    while True:
        try:
            await run_agent(prefix, adapter)
            # run() returned without error — WebSocket closed cleanly.
            print(f"[{adapter.name}] WebSocket closed, reconnecting in {RECONNECT_DELAY}s…")
        except asyncio.CancelledError:
            raise  # honour graceful shutdown
        except Exception as exc:
            print(f"[{adapter.name}] disconnected: {exc!r}, reconnecting in {RECONNECT_DELAY}s…")
            traceback.print_exc()
        await asyncio.sleep(RECONNECT_DELAY)


async def main() -> None:
    await asyncio.gather(
        run_forever("BAND_AGENT1", DataCollectionAgent()),
        run_forever("BAND_AGENT2", PayrollCalculatorAgent()),
        run_forever("BAND_AGENT3", ComplianceReviewAgent()),
        run_forever("BAND_AGENT4", ReportGeneratorAgent()),
    )


asyncio.run(main())
