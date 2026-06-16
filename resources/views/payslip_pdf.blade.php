<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; padding: 32px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #6366f1; padding-bottom: 16px; margin-bottom: 20px; }
        .header-title { font-size: 22px; font-weight: 700; color: #6366f1; letter-spacing: -0.5px; }
        .header-sub { font-size: 11px; color: #6b7280; margin-top: 3px; }
        .header-meta { text-align: right; color: #6b7280; font-size: 10px; line-height: 1.6; }

        .employee-box { background: #f5f6f9; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; }
        .employee-box table { width: 100%; }
        .employee-box td { padding: 2px 8px 2px 0; }
        .employee-box .label { color: #6b7280; width: 120px; }
        .employee-box .value { font-weight: 600; }

        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 8px; }

        table.ledger { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.ledger th { background: #6366f1; color: #fff; text-align: left; padding: 7px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        table.ledger td { padding: 7px 10px; border-bottom: 1px solid #e9ebf1; }
        table.ledger tr:last-child td { border-bottom: none; }
        table.ledger .amount { text-align: right; font-variant-numeric: tabular-nums; }
        table.ledger .deduction { color: #dc2626; }
        table.ledger .total-row td { font-weight: 700; background: #eef2ff; font-size: 12px; border-top: 2px solid #6366f1; }

        .correction-note { background: #fef9c3; border-left: 3px solid #ca8a04; padding: 8px 12px; border-radius: 4px; font-size: 10px; color: #854d0e; margin-bottom: 20px; }

        .footer { border-top: 1px solid #e9ebf1; padding-top: 12px; color: #9ca3af; font-size: 9px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-title">PAYSLIP</div>
        <div class="header-sub">Payroll Management System — Carlsson Studio</div>
    </div>
    <div class="header-meta">
        Period: <strong>{{ $payslip->period }}</strong><br>
        Generated: {{ $payslip->created_at->format('d M Y') }}<br>
        Ref #{{ str_pad($payslip->id, 6, '0', STR_PAD_LEFT) }}
    </div>
</div>

<div class="employee-box">
    <table>
        <tr>
            <td class="label">Employee</td>
            <td class="value">{{ $employee->first_name }} {{ $employee->last_name }}</td>
            <td class="label">Employee ID</td>
            <td class="value">#{{ $employee->id }}</td>
        </tr>
        <tr>
            <td class="label">Position</td>
            <td class="value">{{ $employee->position }}</td>
            <td class="label">Payment Method</td>
            <td class="value">{{ ucfirst($employee->pay_method ?? '—') }}</td>
        </tr>
    </table>
</div>

<div class="section-title">Earnings &amp; Deductions</div>
<table class="ledger">
    <thead>
        <tr><th>Description</th><th class="amount">Amount (Rp)</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>Gross Pay</td>
            <td class="amount">{{ number_format($payslip->gross_amount) }}</td>
        </tr>
        <tr>
            <td class="deduction">PPh 21 Income Tax</td>
            <td class="amount deduction">− {{ number_format($payslip->pph21) }}</td>
        </tr>
        <tr>
            <td class="deduction">BPJS Deductions (Kesehatan + TK)</td>
            <td class="amount deduction">− {{ number_format($payslip->bpjs_total) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Pay</td>
            <td class="amount">{{ number_format($payslip->paidAmount()) }}</td>
        </tr>
    </tbody>
</table>

@if ($payslip->corrected_amount)
<div class="correction-note">
    ⚠ Original net pay was Rp {{ number_format($payslip->net_amount) }}.
    Amount was manually corrected to Rp {{ number_format($payslip->corrected_amount) }} by payroll administrator.
</div>
@endif

<div class="footer">
    <span>This payslip was generated automatically by the payroll pipeline.</span>
    <span>{{ $payslip->period }} · {{ $employee->first_name }} {{ $employee->last_name }}</span>
</div>

</body>
</html>
