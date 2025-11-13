@extends('member')

@section('title', 'Employee Data')

@section('content')
<div id="content">
    <div class="inner" >
        <form action="/employee" method="post">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="pull-left"> Employee Data </h2>
                    <h2><button class="btn btn-primary pull-right" type="submit">Submit</button></h2>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"> Personal </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label> First Name </label>
                                <input name="first_name" class="form-control" value="{{ $first_name ?? '' }}"/>
                            </div>
                            <div class="form-group">
                                <label> Last Name </label>
                                <input name="last_name" class="form-control" value="{{ $last_name ?? '' }}"/>
                            </div>
                            <div class="form-group">
                                <label> Date of Birth </label>
                                <input type="date" name="dob" class="form-control" value="{{ $dob ?? '' }}"/>
                            </div>
                            <div class="form-group">
                                <label> Email </label>
                                <input class="form-control" name="email" value="{{ $email ?? '' }}" />
                            </div>
                            <div class="form-group">
                                <label> Contact </label>
                                <input class="form-control" name="contact" value="{{ $contact ?? '' }}" />
                            </div>
                            <div class="form-group">
                                <label> Address </label>
                                <input class="form-control" name="address" value="{{ $address ?? '' }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"> Employment Scheme </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label> Position-POS </label>
                                <select class="form-control" name="position">
                                    <option value="Employee" {{ isset($position) && $position == 'Employee' ? 'selected' : '' }}>Employee</option>
                                    <option value="Manager" {{ isset($position) && $position == 'Manager' ? 'selected' : '' }}>Manager</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Employment Scheme</label>
                                <select class="form-control" name="scheme">
                                    <option selected> </option>
                                    <option value="HOURLY" {{ isset($scheme) && $scheme == 'HOURLY' ? 'selected' : '' }}>Hourly basis</option>
                                    <option value="MONTHLY" {{ isset($scheme) && $scheme == 'MONTHLY' ? 'selected' : '' }}>Salaried Employee</option>
                                    <option value="COMMISSION" {{ isset($scheme) && $scheme == 'COMMISSION' ? 'selected' : '' }}>Commision</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                            	<label>Base Amount</label>
                                <input type="number" class="form-control" name="base_amount" value="{{ $base_amount ?? '' }}" />
                            </div>
                            <div class="form-group">
                                <label>Commision Rate (only applicable for Commision employment scheme)</label>
                                <input type="number" class="form-control" name="base_commision_rate" value="{{ $base_commision_rate ?? '' }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"> Extras </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label> Payment Method </label>
                                <select name="pay_method" class="form-control">
                                    <option value="cash" {{ isset($pay_method) && $pay_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="delver" {{ isset($pay_method) && $pay_method == 'delver' ? 'selected' : '' }}>Delivered to Postal Address</option>
                                    <option value="trnsfr" {{ isset($pay_method) && $pay_method == 'trnsfr' ? 'selected' : '' }}>Transfer to Bank Account</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label> Bank </label> 
                                <input name="bank" class="form-control" value="{{ $bank ?? '' }}" />
                            </div>
                            <div class="form-group">
                                <label> Bank Account </label> 
                                <input name="bank_account" class="form-control" value="{{ $bank_account ?? '' }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
        </form>
    </div>
</div>
@endsection