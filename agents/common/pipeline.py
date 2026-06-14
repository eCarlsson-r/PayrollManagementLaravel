"""Shared Band wiring for the four-agent payroll pipeline.

Pipeline order (handoff via @mention):
    Data Collector -> Payroll Calculator -> Compliance Reviewer -> Report Generator

Each agent passes the payroll batch forward as a JSON object embedded in the
chat message. Routing is mention-based, so an agent only receives a message
when the previous agent @mentions it by name.

NOTE: band-sdk imports under the `band` package (the project was renamed from
"thenvoi"). Verified against band-sdk 1.0.0. If a future version moves these,
adjust the imports here in one place.
"""

from __future__ import annotations

import calendar
import datetime
import json
import os
import re
import traceback

from band import Agent
from band.core.simple_adapter import SimpleAdapter

# --- Agent display names (must match the agents created in your Band workspace) ---
DATA_COLLECTOR = "Data Collector"
PAYROLL_CALCULATOR = "Payroll Calculator"
COMPLIANCE_REVIEWER = "Compliance Reviewer"
REPORT_GENERATOR = "Report Generator"


# --------------------------------------------------------------------------- #
# Date helpers
# --------------------------------------------------------------------------- #
def month_end(value: str | None = None) -> str:
    """Return the YYYY-MM-DD of the last day of a month.

    `value` may be "YYYY-MM", "YYYY-MM-DD", or None (current month). The
    Laravel calculator only emits MONTHLY/COMMISSION base pay on month-end,
    so Agent 1 calls calculate() with this date.
    """
    if value:
        y, m = value.split("-")[0:2]
        d = datetime.date(int(y), int(m), 1)
    else:
        d = datetime.date.today()
    last = calendar.monthrange(d.year, d.month)[1]
    return f"{d.year:04d}-{d.month:02d}-{last:02d}"


def parse_period(text: str) -> str | None:
    """Pull a YYYY-MM or YYYY-MM-DD period out of a free-text trigger message."""
    m = re.search(r"\b(\d{4}-\d{2}(?:-\d{2})?)\b", text or "")
    return m.group(1) if m else None


# --------------------------------------------------------------------------- #
# Base adapter
# --------------------------------------------------------------------------- #
class BasePayrollAdapter(SimpleAdapter):
    """Common message handling for every payroll agent.

    Subclasses set `name` / `next_agent` and implement `handle()`.
    """

    name: str = "Base"
    next_agent: str | None = None

    def __init__(self):
        super().__init__(history_converter=None)

    async def on_message(
        self,
        msg,
        tools,
        history,
        participants_msg,
        contacts_msg=None,
        *,
        is_session_bootstrap,
        room_id,
    ) -> None:
        # Ignore system notices; process all user messages (bootstrap or live).
        if getattr(msg, "sender_type", None) == "System":
            return
        try:
            await self.handle(msg, tools)
        except Exception as exc:
            # Print the full traceback to this agent's own terminal/log (it was
            # previously swallowed), and post a visible chat message so failures
            # are obvious in the room during a demo.
            traceback.print_exc()
            try:
                await tools.send_message(f"⚠️ {self.name} failed: {exc}")
            except Exception:
                pass

    async def handle(self, msg, tools) -> None:  # pragma: no cover - overridden
        raise NotImplementedError

    # --- helpers ---------------------------------------------------------- #
    @staticmethod
    def extract_json(content: str) -> dict | None:
        """Best-effort extraction of the JSON payload from a message body
        (which may be prefixed with an @mention)."""
        if not content:
            return None
        start = content.find("{")
        end = content.rfind("}")
        if start == -1 or end == -1 or end < start:
            return None
        try:
            return json.loads(content[start : end + 1])
        except json.JSONDecodeError:
            return None

    async def handoff(self, tools, payload: dict, summary: str | None = None) -> None:
        """Send `payload` to the next agent via @mention."""
        if not self.next_agent:
            raise RuntimeError(f"{self.name} has no next_agent configured")
        body = json.dumps(payload, ensure_ascii=False)
        prefix = f"@{self.next_agent} "
        note = f"{summary}\n" if summary else ""
        await tools.send_message(prefix + note + body, mentions=[self.next_agent])

    async def finish(self, tools, text: str) -> None:
        """Terminal message addressed to the human(s) in the room.

        Band requires at least one mention on every message. We mention only
        the human participants (type "User") — never the agents, since the
        report text can contain a period like "2026-06" that would re-trigger
        the Data Collector and loop the pipeline.
        """
        def field(p, name):
            return p.get(name) if isinstance(p, dict) else getattr(p, name, None)

        try:
            participants = await tools.get_participants() or []
        except Exception:
            participants = []

        humans = [
            field(p, "handle") or field(p, "id")
            for p in participants
            if field(p, "type") == "User"
        ]
        humans = [h for h in humans if h]

        if humans:
            await tools.send_message(text, mentions=humans)
        else:
            # No human to address; emit as an event so we never crash on the
            # "at least one mention required" rule.
            await tools.send_event(text, message_type="task")


# --------------------------------------------------------------------------- #
# Runner
# --------------------------------------------------------------------------- #
async def run_agent(env_prefix: str, adapter: BasePayrollAdapter) -> None:
    """Run the agent forever. (.env is loaded at common package import.)"""
    print(f"\n{'='*60}")
    print(f"Starting {adapter.name}...")
    print(f"  Agent ID: {os.environ[f'{env_prefix}_ID'][:20]}...")
    print(f"  WebSocket: {os.getenv('THENVOI_WS_URL', 'wss://app.band.ai/api/v1/socket/websocket')}")
    print(f"{'='*60}\n")
    agent = Agent.create(
        adapter=adapter,
        agent_id=os.environ[f"{env_prefix}_ID"],
        api_key=os.environ[f"{env_prefix}_KEY"],
        ws_url=os.getenv("THENVOI_WS_URL", "wss://app.band.ai/api/v1/socket/websocket"),
        rest_url=os.getenv("THENVOI_REST_URL", "https://app.band.ai/"),
    )
    print(f"[{adapter.name}] Connected to Band, waiting for messages...\n")
    await agent.run()
