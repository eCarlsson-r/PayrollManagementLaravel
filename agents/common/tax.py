"""Indonesian payroll deductions: PPh 21 progressive income tax + BPJS.

Scope note (hackathon): this implements the brackets/rates given in the
project spec directly. It deliberately *omits* real-world refinements such as
PTKP (non-taxable income), biaya jabatan (5% occupational cost deduction),
the NPWP surcharge, and BPJS wage caps. Those would be added for production
accuracy; the structure here keeps each piece in its own function so they are
easy to extend.

All amounts are in IDR (rupiah).
"""

from __future__ import annotations

from dataclasses import dataclass, asdict

# PPh 21 progressive brackets (2024), keyed on *annual* taxable income.
# Each tuple is (upper_bound_exclusive, rate). The final bound is infinity.
PPH21_BRACKETS = [
    (60_000_000, 0.05),
    (250_000_000, 0.15),
    (500_000_000, 0.25),
    (750_000_000, 0.30),
    (float("inf"), 0.35),
]

# BPJS employee-side contribution rates (as a fraction of monthly gross).
BPJS_EMPLOYEE_RATES = {
    "kesehatan": 0.01,      # health: 1% employee (4% employer)
    "jht": 0.02,            # old-age (Ketenagakerjaan JHT): 2% employee (3.7% employer)
    "jp": 0.01,             # pension (Jaminan Pensiun): 1% employee (2% employer)
}

# Employer-side rates, kept for reporting/completeness.
BPJS_EMPLOYER_RATES = {
    "kesehatan": 0.04,
    "jht": 0.037,
    "jp": 0.02,
}


def annual_pph21(annual_taxable_income: float) -> float:
    """Progressive PPh 21 on an annual taxable income."""
    income = max(0.0, annual_taxable_income)
    tax = 0.0
    lower = 0.0
    for upper, rate in PPH21_BRACKETS:
        if income <= lower:
            break
        taxed = min(income, upper) - lower
        tax += taxed * rate
        lower = upper
    return round(tax, 2)


def bpjs_employee(monthly_gross: float) -> dict[str, float]:
    """Per-program employee BPJS deductions for one month."""
    return {name: round(monthly_gross * rate, 2) for name, rate in BPJS_EMPLOYEE_RATES.items()}


@dataclass
class Deductions:
    gross: float
    pph21_monthly: float
    bpjs: dict[str, float]
    bpjs_total: float
    total_deductions: float
    net: float

    def to_dict(self) -> dict:
        return asdict(self)


def compute_deductions(monthly_gross: float) -> Deductions:
    """Compute monthly PPh 21 + BPJS for a single monthly gross amount.

    PPh 21 is annualized (gross x 12), taxed via the brackets, then divided
    back to a monthly figure — the standard monthly-withholding approach.
    """
    monthly_gross = round(float(monthly_gross), 2)

    annual_tax = annual_pph21(monthly_gross * 12)
    pph21_monthly = round(annual_tax / 12, 2)

    bpjs = bpjs_employee(monthly_gross)
    bpjs_total = round(sum(bpjs.values()), 2)

    total = round(pph21_monthly + bpjs_total, 2)
    net = round(monthly_gross - total, 2)

    return Deductions(
        gross=monthly_gross,
        pph21_monthly=pph21_monthly,
        bpjs=bpjs,
        bpjs_total=bpjs_total,
        total_deductions=total,
        net=net,
    )


if __name__ == "__main__":
    # Quick self-test / worked example (Andi: Rp 8,000,000/month).
    d = compute_deductions(8_000_000)
    print("Worked example — monthly gross Rp 8,000,000")
    print(f"  PPh 21 (annualized 96M): annual={annual_pph21(96_000_000):,.0f}  monthly={d.pph21_monthly:,.0f}")
    print(f"  BPJS employee: {d.bpjs}  total={d.bpjs_total:,.0f}")
    print(f"  Total deductions={d.total_deductions:,.0f}  Net={d.net:,.0f}")
    # Expected: annual tax 8,400,000 -> 700,000/mo; BPJS 320,000; net 6,980,000
    assert d.pph21_monthly == 700_000, d.pph21_monthly
    assert d.bpjs_total == 320_000, d.bpjs_total
    assert d.net == 6_980_000, d.net
    print("OK")
