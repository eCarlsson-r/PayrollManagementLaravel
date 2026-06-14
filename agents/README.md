# Band Agent Pipeline

Four Python agents that automate a payroll run by collaborating in a Band chat
room. They talk to the Laravel payroll API (the Day-1 layer) and to each other
via `@mention` handoffs.

```
┌──────────────────┐   @Payroll Calculator   ┌────────────────────┐
│ 1. Data Collector│ ───────────────────────▶│ 2. Payroll Calc.   │
│ GET /calculate   │                          │ PPh 21 + BPJS      │
└──────────────────┘                          └─────────┬──────────┘
        ▲ trigger (@Data Collector run 2026-06)         │ @Compliance Reviewer
        │ human / orchestrator                          ▼
┌──────────────────┐   final report           ┌────────────────────┐
│ 4. Report Gen.   │ ◀───────────────────────│ 3. Compliance Rev. │
│ POST /submit     │   @Report Generator      │ UMR check, POST /flag│
└──────────────────┘                          └────────────────────┘
```

Each agent forwards the payroll batch as a JSON object embedded in its message.
Because Band routing is mention-based, an agent only wakes up when the previous
stage `@mentions` it.

## Layout

| File | Role |
|------|------|
| `agent1_data_collection.py` | Calls `GET /api/payroll/calculate?date=<month-end>` and seeds the pipeline |
| `agent2_payroll_calculator.py` | Adds PPh 21 progressive tax + BPJS employee deductions (gross → net) |
| `agent3_compliance_review.py` | Validates rows against UMR, posts anomalies to `POST /api/payroll/flag` |
| `agent4_report_generator.py` | Persists net pay via `POST /api/payroll/submit`, renders the payslip report |
| `common/payroll_api.py` | Async client for the Laravel `/api/payroll/*` endpoints |
| `common/tax.py` | PPh 21 brackets + BPJS rates (pure functions, self-testable) |
| `common/pipeline.py` | Agent names, date helpers, `BasePayrollAdapter`, runner |

## Setup

1. **Create four agents** in your Band workspace (https://app.band.ai) named
   exactly: `Data Collector`, `Payroll Calculator`, `Compliance Reviewer`,
   `Report Generator`. (Names matter — they're how agents address each other.
   Change them in one place: `common/pipeline.py`.)

2. **Install deps in a virtual environment** (strongly recommended — keeps the
   build away from your global Python):
   ```bash
   cd agents
   python3 -m venv .venv
   source .venv/bin/activate
   python -m pip install --upgrade pip
   pip install -r requirements.txt
   ```
   `band-sdk` pulls in `cryptography`. If you hit *"Failed building wheel for
   cryptography"*, your resolver tried to compile it from source (needs the Rust
   toolchain). Force the prebuilt wheel instead:
   ```bash
   pip install --only-binary=cryptography -r requirements.txt
   ```

3. **Configure env**:
   ```bash
   cp .env.example .env
   ```
   Fill in each agent's id/key, and set `PAYROLL_API_URL` + `PAYROLL_API_KEY`
   (the latter must equal `BAND_API_KEY` in the Laravel `.env`).

## Running

Run each agent in its own process (they connect and wait for messages):

```bash
cd agents
python3 agent1_data_collection.py   # terminal 1
python3 agent2_payroll_calculator.py # terminal 2
python3 agent3_compliance_review.py  # terminal 3
python3 agent4_report_generator.py   # terminal 4
```

Then, in the Band room, trigger a run:

```
@Data Collector run payroll for 2026-06
```

Agent 1 resolves the period to **month-end** (`2026-06-30`) before calling
`calculate`, so the full MONTHLY/COMMISSION set is returned — off month-end the
Laravel calculator returns HOURLY rows only.

## Verifying without Band

The deterministic parts run offline:

```bash
python3 common/tax.py     # PPh 21 / BPJS worked example with assertions
```

The tax math and the agent-to-agent data flow have been exercised offline
against the real installed `band-sdk` (imports, `SimpleAdapter.on_message`
signature, and `tools.send_message`/`send_event` are all verified). What has
**not** been run is the live platform loop (`Agent.create` → WebSocket →
`@mention` handoff in a real room) — that needs the four agents created in a
Band workspace and their credentials in `.env`.

> Heads-up: the public docs at https://docs.band.ai still use the old package
> name `thenvoi`. The installed SDK imports under `band` (e.g. `from band import
> Agent`); the code here already uses the `band` names.

## Known limitations (intentional scope)

- **Tax model is simplified** per the project spec: PPh 21 is applied to the
  annualized gross via the brackets only. Real payroll also subtracts PTKP,
  biaya jabatan (5%), BPJS-before-tax, and applies the NPWP surcharge and BPJS
  wage caps. See the scope note in `common/tax.py`.
- **UMR check treats every gross as monthly.** HOURLY rows from the calculator
  are a *weekly* total, so an hourly worker can be flagged as "below UMR" even
  when their monthly pay is fine. For a faithful check, normalize HOURLY to a
  monthly figure before comparing — left as a follow-up.
