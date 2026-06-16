<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Instrument Sans';
            font-style: normal;
            font-weight: 400;
            src: url('{{ public_path("fonts/InstrumentSans-Regular.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Instrument Sans';
            font-style: italic;
            font-weight: 400;
            src: url('{{ public_path("fonts/InstrumentSans-Italic.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Instrument Sans';
            font-style: normal;
            font-weight: 600;
            src: url('{{ public_path("fonts/InstrumentSans-SemiBold.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Instrument Sans';
            font-style: normal;
            font-weight: 700;
            src: url('{{ public_path("fonts/InstrumentSans-Bold.ttf") }}') format('truetype');
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            padding: 36px 40px;
            background: #fff;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header-table { width: 100%; border-collapse: collapse; padding-bottom: 14px; margin-bottom: 18px; border-bottom: 2.5px solid #6366f1; }
        .header-title { font-size: 26px; font-weight: 700; color: #6366f1; letter-spacing: -0.5px; }
        .header-sub   { font-size: 10.5px; color: #6b7280; margin-top: 3px; }
        .header-meta  { text-align: right; color: #6b7280; font-size: 10px; line-height: 1.8; vertical-align: top; }
        .header-meta strong { color: #1f2937; }

        /* ── Employee box ────────────────────────────────────── */
        .emp-box { background: #f5f6f9; border-radius: 8px; padding: 12px 16px; margin-bottom: 22px; }
        .emp-table { width: 100%; border-collapse: collapse; }
        .emp-table td { padding: 3px 6px 3px 0; font-size: 11px; }
        .emp-label  { color: #6b7280; width: 110px; }
        .emp-value  { font-weight: 700; color: #111827; }

        /* ── Section title ───────────────────────────────────── */
        .section-title {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #6b7280;
            margin-bottom: 8px;
        }

        /* ── Ledger table ────────────────────────────────────── */
        .ledger { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .ledger th {
            background: #6366f1;
            color: #fff;
            text-align: left;
            padding: 8px 12px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .ledger th.amount { text-align: right; }
        .ledger td { padding: 8px 12px; border-bottom: 1px solid #e9ebf1; font-size: 11px; }
        .ledger td.amount { text-align: right; font-variant-numeric: tabular-nums; }
        .ledger .deduction td { color: #dc2626; }
        .ledger .total td {
            font-weight: 700;
            font-size: 12.5px;
            background: #eef2ff;
            border-top: 2px solid #6366f1;
            border-bottom: none;
            padding: 10px 12px;
        }

        /* ── Correction note ─────────────────────────────────── */
        .correction {
            background: #fef9c3;
            border-left: 3px solid #ca8a04;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 10px;
            color: #854d0e;
            margin-bottom: 22px;
        }
        .correction-label {
            display: inline-block;
            background: #ca8a04;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 1px 5px;
            border-radius: 3px;
            margin-right: 6px;
            text-transform: uppercase;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #e9ebf1;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer-table td { font-size: 9px; color: #9ca3af; padding-top: 10px; }
        .footer-right { text-align: right; }
    </style>
</head>
<body>

{{-- Header --}}
<table class="header-table">
    <tr>
        <td style="vertical-align:top">
            <div class="header-title">PAYSLIP</div>
            <div class="header-sub">Payroll Management System &mdash; Carlsson Studio</div>
        </td>
        <td class="header-meta">
            Period: <strong>{{ $payslip->period }}</strong><br>
            Generated: {{ $payslip->created_at->format('d M Y') }}<br>
            Ref #{{ str_pad($payslip->id, 6, '0', STR_PAD_LEFT) }}
        </td>
    </tr>
</table>

{{-- Employee info --}}
<div class="emp-box">
    <table class="emp-table">
        <tr>
            <td class="emp-label">Employee</td>
            <td class="emp-value">{{ $employee->first_name }} {{ $employee->last_name }}</td>
            <td class="emp-label">Employee ID</td>
            <td class="emp-value">#{{ $employee->id }}</td>
        </tr>
        <tr>
            <td class="emp-label">Position</td>
            <td class="emp-value">{{ $employee->position }}</td>
            <td class="emp-label">Payment Method</td>
            <td class="emp-value">{{ ucfirst($employee->pay_method ?? '—') }}</td>
        </tr>
    </table>
</div>

{{-- Ledger --}}
<div class="section-title">Earnings &amp; Deductions</div>
<table class="ledger">
    <thead>
        <tr>
            <th>Description</th>
            <th class="amount">Amount (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gross Pay</td>
            <td class="amount">{{ number_format($payslip->gross_amount) }}</td>
        </tr>
        <tr class="deduction">
            <td>PPh 21 Income Tax</td>
            <td class="amount">&minus; {{ number_format($payslip->pph21) }}</td>
        </tr>
        <tr class="deduction">
            <td>BPJS Deductions (Kesehatan + TK)</td>
            <td class="amount">&minus; {{ number_format($payslip->bpjs_total) }}</td>
        </tr>
        <tr class="total">
            <td>Net Pay</td>
            <td class="amount">{{ number_format($payslip->paidAmount()) }}</td>
        </tr>
    </tbody>
</table>

{{-- Correction note --}}
@if ($payslip->corrected_amount)
<div class="correction">
    <span class="correction-label">Adjusted</span>Original net pay was Rp {{ number_format($payslip->net_amount) }}.
    Amount was manually corrected to Rp {{ number_format($payslip->corrected_amount) }} by the payroll administrator.
</div>
@endif

{{-- Footer --}}
<table class="footer-table">
    <tr>
        <td>This payslip was generated automatically by the payroll pipeline.</td>
        <td class="footer-right">{{ $payslip->period }} &middot; {{ $employee->first_name }} {{ $employee->last_name }}</td>
    </tr>
</table>

</body>
</html>
