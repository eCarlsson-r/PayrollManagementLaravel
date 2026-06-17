import asyncio

from agent1_data_collection import DataCollectionAgent
from agent2_payroll_calculator import PayrollCalculatorAgent
from agent3_compliance_review import ComplianceReviewAgent
from agent4_report_generator import ReportGeneratorAgent
from common.pipeline import run_agent


async def main() -> None:
    await asyncio.gather(
        run_agent("BAND_AGENT1", DataCollectionAgent()),
        run_agent("BAND_AGENT2", PayrollCalculatorAgent()),
        run_agent("BAND_AGENT3", ComplianceReviewAgent()),
        run_agent("BAND_AGENT4", ReportGeneratorAgent()),
    )


asyncio.run(main())
