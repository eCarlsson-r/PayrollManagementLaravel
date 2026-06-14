"""Thin OpenAI-compatible LLM wrapper for the two hackathon providers.

Both AI/ML API and Featherless expose an OpenAI-compatible Chat Completions
API, so we reuse the official `openai` async client and just point it at the
right base_url. Each agent picks a provider by name.

Design goal: the LLM is *optional enrichment*. If the provider key is missing
or the call fails, callers fall back to deterministic text — the payroll
pipeline never depends on an LLM for correctness (money math stays in tax.py).
"""

from __future__ import annotations

import os

# provider -> (api_key_env, base_url, model_env, default_model)
PROVIDERS = {
    "aimlapi": (
        "AIMLAPI_API_KEY",
        "https://api.aimlapi.com/v1",
        "AIMLAPI_MODEL",
        "gpt-4o-mini",
    ),
    "featherless": (
        "FEATHERLESS_API_KEY",
        "https://api.featherless.ai/v1",
        "FEATHERLESS_MODEL",
        "meta-llama/Meta-Llama-3.1-8B-Instruct",
    ),
}


class LLM:
    def __init__(self, provider: str):
        if provider not in PROVIDERS:
            raise ValueError(f"Unknown LLM provider: {provider}")
        self.provider = provider
        key_env, base_url, model_env, default_model = PROVIDERS[provider]
        self._api_key = os.getenv(key_env)
        self._base_url = base_url
        self._model = os.getenv(model_env, default_model)

    @property
    def configured(self) -> bool:
        return bool(self._api_key)

    async def complete(self, system: str, user: str, *, max_tokens: int = 400) -> str | None:
        """Return the model's text, or None if unconfigured / on any error
        (so callers can fall back to deterministic output)."""
        if not self.configured:
            return None
        try:
            from openai import AsyncOpenAI

            # Short timeout + minimal retries: the LLM is optional enrichment,
            # so fall back to deterministic text fast rather than stalling the
            # pipeline if a provider is slow or cold-starting.
            client = AsyncOpenAI(
                api_key=self._api_key,
                base_url=self._base_url,
                timeout=30,
                max_retries=1,
            )
            resp = await client.chat.completions.create(
                model=self._model,
                messages=[
                    {"role": "system", "content": system},
                    {"role": "user", "content": user},
                ],
                max_tokens=max_tokens,
                temperature=0.3,
            )
            return (resp.choices[0].message.content or "").strip()
        except Exception:
            return None
