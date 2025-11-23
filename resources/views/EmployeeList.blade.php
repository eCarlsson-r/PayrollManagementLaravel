@extends('member')

@section('title', 'Employee List')

@section('content')
<form action="/employee/" method="post" role="form">        
    @csrf
    @method("DELETE")
    <div class="row">
        <div class="col-lg-12">
            <h2 class="pull-left"> Employee Management </h2>
            <h2 class="pull-right">
                <button type="submit" class="btn btn-secondary" name="delete">Delete Employee</button>
                <button type="reset" class="btn btn-secondary" name="clear">Reset Selection</button>
                <a href="/employee/create" class="btn btn-primary">Add New Employee</a>
            </h2>
        </div>
    </div>
    <hr />
    <div class="mb-3 table-responsive">
        <table data-toggle="table" class="table table-striped table-bordered table-hover">
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
</form>
@endsection