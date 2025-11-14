@extends('member')

@section('title', 'Proceed Payment')

@section('content')
<div id="content">
    <div class="inner" >
        <div class="row">
            <div class="col-lg-12">
                <h2> Proceed Payment </h2>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading"> Payments to be made </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
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
                                        <td>{{ $Apymt["employee_id"] }}</td>
                                        <td>{{ $Apymt["base_amount"] }}</td>
                                        <td>{{ $Apymt["hours_worked"] }}</td>
                                        <td>{{ $Apymt["late_days"] }}</td>
                                        <td>{{ $Apymt["commision"] }}</td>
                                        <td>{{ $Apymt["amount"] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div>
@endsection


@section('script')
<script>
    $(document).ready(function () { 
        $('#dataTables-example').dataTable();
    });
</script>
@endsection