@extends('member')

@section('title', 'Employee List')

@section('content')
    <div id="content">
        <div class="inner" >
            <div class="row">
                <div class="col-lg-12">
                    <h2> Employee Management </h2>
                </div>
            </div>
            <hr />
            <div class="panel-body">
                <div class="panel-group" id="accordion" >
                    <div class="form-group">
                        <div class="table-responsive">
                            <form action="/employee/" method="post" role="form">
                                @csrf
                                @method("DELETE")
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Employee ID</th>
                                            <th>Employee Name</th>
                                            <th>Employee's Manager</th>
                                            <th>E-mail Address</th>
                                            <th>Postal Address</th>
                                            <th>Payment Method</th>
                                            <th>Bank Account</th>
                                        </tr> 
                                    </thead>
                                    <tbody>	
                                        @foreach ($employees as $employee)
                                        <tr>
                                            <td><input type="radio" name="employee" value="{{ $employee -> id }}"></td>
                                            <td>{{ $employee->id }}</td>
                                            <td>{{ $employee->first_name." ".$employee->last_name }}</td>
                                            <td>{{ $employee->manager }}</td>
                                            <td>{{ $employee->email }}</td>
                                            <td>{{ $employee->address }}</td>
                                            <td>{{ $employee->pay_method }}</td>
                                            <td>{{ $employee->bank_account }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-default" name="delete">Delete Employee</button>
                            &emsp;
                            <button type="reset" class="btn btn-default" name="clear">Reset Selection</button>
                            &emsp;
                            <a href="/employee/create" class="btn btn-primary">Add New Employee</a>
                        </form>
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