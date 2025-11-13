@extends('member')

@section('title', 'Profile')

@section('content')
<div id="content">
    <div class="inner">
        <form action={{ '/employee/'.$id }} method="post" role="form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="pull-left"> Profile </h2>
                    <h2><button class="btn btn-primary pull-right" type="submit">Save changes</button></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <hr />
                    <nav class="navbar navbar-default">
                        <ul class="nav navbar-nav">
                            <li><a href="#personal" data-toggle="tab">Personal</a></li>
                            <li class="active"><a href="#account" data-toggle="tab">Account</a></li>
                            <li><a href="#extras" data-toggle="tab">Extras</a></li>
                            <li class="pull-right"></li>
                        </ul>
                    </nav>
                    <div class="tab-content">
                        <div class="tab-pane" id="personal">
                            <div class="form-group">
                                <label> First Name </label> 
                                <input name="first_name" class="form-control" value="{{ $first_name ?? '' }}" readonly/>
                            </div>
                            <div class="form-group">
                                <label> Last Name </label>
                                <input name="last_name" class="form-control" value="{{ $last_name ?? '' }}" readonly/>
                            </div>
                            <div class="form-group"> 
                                <label> Date of Birth </label>
                                <input type="date" name="dob" class="form-control" value="{{ $dob ?? '' }}" readonly/>
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
                        <div class="tab-pane active" id="account">
                            <div class="form-group">
                                <label> Email </label>
                                <input class="form-control" name="email" value="{{ $email ?? '' }}" />
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" value="{{ $password ?? '' }}"/>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label> 
                                <input type="password" name="password_confirmation" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label> Position-POS </label>
                                <select name="position" class="form-control" disabled>
                                    <option value="Employee" {{ isset($position) && $position == 'Employee' ? 'selected' : '' }}>Staff</option>
                                    <option value="Manager" {{ isset($position) && $position == 'Manager' ? 'selected' : '' }}>Manager</option>
                                </select>
                            </div>
                        </div>
                        <div class="tab-pane" id="extras">
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
                            
                            <div class="form-group">
                                <label> Work History </label>
                                <select name="workhistory" class="form-control" multiple size="10" readonly>
                                    @foreach ($career as $whst)
                                        <option>{{ $whst['position'] }}&emsp; From: {{ $whst['start_date'] }}&emsp; To: {{ $whst['end_date'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
        </form>
    </div>
</div>	
@endsection