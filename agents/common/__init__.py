"""Package init: load agents/.env deterministically.

This runs the moment any `common.*` module is imported — before adapters are
instantiated or PayrollClient/LLM read os.environ. We pass an explicit path
(rather than dotenv's find_dotenv, which walks frames and is cwd/context
fragile) so it works regardless of where the agent process is launched from.
"""

import os
from pathlib import Path

import certifi
from dotenv import load_dotenv

# agents/.env  (this file is agents/common/__init__.py -> parent.parent == agents/)
load_dotenv(dotenv_path=Path(__file__).resolve().parent.parent / ".env")

# macOS python.org framework builds don't ship a CA bundle for the stdlib `ssl`
# module, so the WebSocket client fails cert verification ("unable to get local
# issuer certificate"). Point OpenSSL at certifi's bundle. setdefault so an
# explicit SSL_CERT_FILE in the environment still wins.
os.environ.setdefault("SSL_CERT_FILE", certifi.where())
