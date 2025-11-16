@extends('member')

@section('title', 'Payment History')

@section('content')
<div id="content">
    <div class="inner" >
        <div class="row">
            <div class="col-lg-12">
                <h2> Payment History </h2>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading"> Payments Made to Me </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table data-toggle="table" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                @if (auth()->user()->type=="Admin")
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($admin_payment as $Apymt)
                                        <tr class="gradeA">
                                            <td>{{ $Apymt->employee_id }}</td>
                                            <td>{{ $Apymt->date }}</td>
                                            <td>{{ $Apymt->amount }}</td>
                                            <td>{{ $Apymt->method }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                @endif

                                @if (auth()->user()->type == "Employee" || auth()->user()->type=="Manager")
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($personal_payment as $Opymt)
                                            <tr>
                                                <td>{{ $Opymt->date }}</td>
                                                <td>{{ $Opymt->amount }}</td>
                                                <td>{{ $Opymt->method }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div>
@endsection