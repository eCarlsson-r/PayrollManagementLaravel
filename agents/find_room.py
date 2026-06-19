"""One-shot helper: print the Band room ID(s) where Agent 1 is a participant.

Run once (locally or via Coolify console) to get the BAND_ROOM_ID value:

    python find_room.py
"""

import asyncio
import os
from pathlib import Path

from dotenv import load_dotenv
from thenvoi_rest import AsyncRestClient

load_dotenv(dotenv_path=Path(__file__).parent / ".env")


async def main() -> None:
    client = AsyncRestClient(
        api_key=os.environ["BAND_AGENT1_KEY"],
        base_url=os.environ.get("THENVOI_REST_URL", "https://app.band.ai/"),
    )
    rooms = await client.agent_api_chats.list_agent_chats()
    if not rooms.data:
        print("No rooms found for BAND_AGENT1.")
        return
    for room in rooms.data:
        print(f"BAND_ROOM_ID={room.id}  title={room.title!r}")


asyncio.run(main())
