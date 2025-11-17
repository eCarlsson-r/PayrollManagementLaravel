@extends('member')

@section('title', 'Proceed Payment')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h2> Proceed Payment </h2>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-lg-12">
        <form role="form" method="POST" action="/payment">
            @csrf
            <div class="panel panel-default">
                <div class="panel-heading">
                    Payments to be made
                    <button type="submit" class="btn btn-primary btn-sm pull-right">
                        Proceed Payment
                    </button>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table data-toggle="table" class="table table-striped table-bordered table-hover" name="payments">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Base Amount</th>
                                    <th>Hours Worked</th>
                                    <th>Days Late</th>
                                    <th>Commission</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($payments_to_made as $Apymt)
                                <tr class="gradeA">
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][employee_id]" readonly value="{{ $Apymt["employee_id"] ?? '' }}"></td>
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][base_amount]" value="{{ $Apymt["base_amount"] }}"></td>
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][hours_worked]" value="{{ $Apymt["hours_worked"] }}"></td>
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][late_days]" value="{{ $Apymt["late_days"] ?? '0' }}"></td>
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][commision]" value="{{ $Apymt["commision"] ?? '0' }}"></td>
                                    <td><input class="form-control" name="payments[{{ $loop->iteration }}][amount]" value="{{ $Apymt["amount"] }}"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div> 
                </div> 
            </div> 
        </form>
    </div> 
</div> 
@endsection