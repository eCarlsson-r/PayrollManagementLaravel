"""Async client for the Laravel payroll API (the Day-1 layer).

Endpoints (all authenticated with the X-API-Key header):
  GET  /api/payroll/calculate?date=YYYY-MM-DD
  POST /api/payroll/submit   {payments: [{employee_id, amount, method?, date?}]}
  POST /api/payroll/flag     single {employee_id, reason, ...} or {flags: [...]}
"""

from __future__ import annotations

import os
from typing import Any

import httpx


class PayrollAPIError(RuntimeError):
    pass


class PayrollClient:
    def __init__(self, base_url: str | None = None, api_key: str | None = None, timeout: float = 30.0):
        self.base_url = (base_url or os.environ["PAYROLL_API_URL"]).rstrip("/")
        self.api_key = api_key or os.environ["PAYROLL_API_KEY"]
        self._timeout = timeout

    def _headers(self) -> dict[str, str]:
        return {"X-API-Key": self.api_key, "Accept": "application/json"}

    async def _request(self, method: str, path: str, **kwargs) -> dict[str, Any]:
        url = f"{self.base_url}{path}"
        async with httpx.AsyncClient(timeout=self._timeout) as client:
            resp = await client.request(method, url, headers=self._headers(), **kwargs)
        try:
            data = resp.json()
        except ValueError:
            raise PayrollAPIError(f"{method} {path} -> {resp.status_code}, non-JSON body: {resp.text[:200]}")
        if resp.status_code >= 400:
            raise PayrollAPIError(f"{method} {path} -> {resp.status_code}: {data}")
        return data

    async def calculate(self, date: str | None = None) -> dict[str, Any]:
        """GET /api/payroll/calculate. Pass a month-end date to include the
        full MONTHLY/COMMISSION set (HOURLY-only otherwise)."""
        params = {"date": date} if date else None
        return await self._request("GET", "/api/payroll/calculate", params=params)

    async def submit(self, payments: list[dict[str, Any]]) -> dict[str, Any]:
        """POST /api/payroll/submit. payments: [{employee_id, amount, method?, date?}]."""
        return await self._request("POST", "/api/payroll/submit", json={"payments": payments})

    async def flag(self, flags: list[dict[str, Any]]) -> dict[str, Any]:
        """POST /api/payroll/flag with a batch of flag records."""
        return await self._request("POST", "/api/payroll/flag", json={"flags": flags})

    async def log(
        self,
        *,
        content: str,
        period: str | None = None,
        agent: str | None = None,
        sender_type: str = "Agent",
        type: str = "message",
    ) -> dict[str, Any]:
        """POST /api/payroll/log — record a chat message for the workflow page."""
        return await self._request(
            "POST",
            "/api/payroll/log",
            json={
                "period": period,
                "agent": agent,
                "sender_type": sender_type,
                "type": type,
                "content": content,
            },
        )

    async def list_flags(self, period: str | None = None) -> dict[str, Any]:
        """GET /api/payroll/flags?period= — flags with their approve/reject decision."""
        params = {"period": period} if period else None
        return await self._request("GET", "/api/payroll/flags", params=params)

    async def payslip(self, payslips: list[dict[str, Any]]) -> dict[str, Any]:
        """POST /api/payroll/payslip — generate PDF payslips for submitted employees."""
        return await self._request("POST", "/api/payroll/payslip", json={"payslips": payslips})
