@extends('member')

@section('title', 'Payslips')

@section('content')
<div class="page-head">
    <h2>Payslips</h2>
</div>
<hr />

@if ($payslips->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            No payslips generated yet. Run the payroll pipeline to produce them.
        </div>
    </div>
@else
    @foreach ($payslips->groupBy('period') as $period => $group)
        <h5 class="fw-600 mb-2 mt-4" style="color:var(--ink-soft)">{{ $period }}</h5>
        <div class="card mb-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Gross</th>
                            <th>PPh 21</th>
                            <th>BPJS</th>
                            <th>Net Pay</th>
                            <th>Note</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group as $slip)
                        <tr>
                            <td>
                                <strong>{{ $slip->employee->first_name }} {{ $slip->employee->last_name }}</strong>
                                <div class="text-muted small">#{{ $slip->employee_id }}</div>
                            </td>
                            <td>Rp {{ number_format($slip->gross_amount) }}</td>
                            <td class="text-danger">− Rp {{ number_format($slip->pph21) }}</td>
                            <td class="text-danger">− Rp {{ number_format($slip->bpjs_total) }}</td>
                            <td class="fw-bold">Rp {{ number_format($slip->paidAmount()) }}</td>
                            <td>
                                @if ($slip->corrected_amount)
                                    <span class="badge text-bg-warning">Corrected</span>
                                @endif
                            </td>
                            <td>
                                <a href="/payslip/{{ $slip->id }}/download"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-download"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
@endsection
