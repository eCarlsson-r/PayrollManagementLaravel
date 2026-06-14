"""Quick test to verify agents can import and connect to Band."""

import asyncio
import os
from pathlib import Path

# Import common first to load .env
import common
from common.payroll_api import PayrollClient
from agent1_data_collection import DataCollectionAgent
from agent2_payroll_calculator import PayrollCalculatorAgent
from agent3_compliance_review import ComplianceReviewAgent
from agent4_report_generator import ReportGeneratorAgent


async def test_api():
    """Test that the Laravel API is reachable."""
    print("Testing Laravel API...")
    client = PayrollClient()
    try:
        result = await client.calculate("2026-06-30")
        count = result.get("count", 0)
        print(f"✓ API OK: {count} payments returned")
        return True
    except Exception as e:
        print(f"✗ API ERROR: {e}")
        return False


async def test_band_config():
    """Verify Band credentials are configured."""
    print("\nTesting Band configuration...")
    for i in range(1, 5):
        agent_id = os.environ.get(f"BAND_AGENT{i}_ID")
        agent_key = os.environ.get(f"BAND_AGENT{i}_KEY")
        if agent_id and agent_key:
            print(f"✓ Agent {i}: credentials found")
        else:
            print(f"✗ Agent {i}: MISSING credentials")
            return False
    return True


async def main():
    print("=" * 60)
    print("DIAGNOSTIC TEST: Band Agent Pipeline")
    print("=" * 60)

    api_ok = await test_api()
    config_ok = await test_band_config()

    print("\n" + "=" * 60)
    if api_ok and config_ok:
        print("✓ All diagnostics passed. Agents should be able to connect.")
        print("\nNEXT STEPS:")
        print("1. Verify agents are in the Band chat room")
        print("2. Check that agent NAMES in Band match: Data Collector, Payroll Calculator, etc.")
        print("3. Restart all 4 agents")
        print("4. Post trigger: @Data Collector run payroll for 2026-06")
    else:
        print("✗ Diagnostics failed. Check errors above.")
    print("=" * 60)


if __name__ == "__main__":
    asyncio.run(main())
