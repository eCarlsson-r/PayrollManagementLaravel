@extends('member')

@section('title', 'Profile')

@section('content')
<form action={{ '/employee/'.$id }} method="post" role="form">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-12">
            <h2 class="pull-left"> Profile </h2>
            <h2 class="pull-right"><button class="btn btn-primary" type="submit">Save changes</button></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
        <hr/>
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link" data-bs-target="#personal" data-bs-toggle="tab">Personal</a></li>
                <li class="nav-item"><a class="nav-link active" data-bs-target="#account" data-bs-toggle="tab">Account</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-target="#extras" data-bs-toggle="tab">Extras</a></li>
            </ul>
            <div class="card card-default tab-content">
                <div class="card-body tab-pane fade" id="personal">
                    <div class="mb-3">
                        <label> First Name </label> 
                        <input name="first_name" class="form-control" value="{{ $first_name ?? '' }}" readonly/>
                    </div>
                    <div class="mb-3">
                        <label> Last Name </label>
                        <input name="last_name" class="form-control" value="{{ $last_name ?? '' }}" readonly/>
                    </div>
                    <div class="mb-3"> 
                        <label> Date of Birth </label>
                        <input type="date" name="dob" class="form-control" value="{{ $dob ?? '' }}" readonly/>
                    </div>
                    <div class="mb-3">
                        <label> Contact </label>
                        <input class="form-control" name="contact" value="{{ $contact ?? '' }}" />
                    </div>
                    <div class="mb-3">
                        <label> Address </label>
                        <input class="form-control" name="address" value="{{ $address ?? '' }}" />
                    </div>
                </div>
                <div class="card-body tab-pane fade show active" id="account">
                    <div class="mb-3">
                        <label> Email </label>
                        <input class="form-control" name="email" value="{{ $email ?? '' }}" />
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" value="{{ $password ?? '' }}"/>
                    </div>
                    <div class="mb-3">
                        <label>Confirm Password</label> 
                        <input type="password" name="password_confirmation" class="form-control"/>
                    </div>
                    <div class="mb-3">
                        <label> Position-POS </label>
                        <select name="position" class="form-control" disabled>
                            <option value="Employee" {{ isset($position) && $position == 'Employee' ? 'selected' : '' }}>Staff</option>
                            <option value="Manager" {{ isset($position) && $position == 'Manager' ? 'selected' : '' }}>Manager</option>
                        </select>
                    </div>
                </div>
                <div class="card-body tab-pane fade" id="extras">
                    <div class="mb-3">
                        <label> Payment Method </label>
                        <select name="pay_method" class="form-control">
                            <option value="cash" {{ isset($pay_method) && $pay_method == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="delver" {{ isset($pay_method) && $pay_method == 'delver' ? 'selected' : '' }}>Delivered to Postal Address</option>
                            <option value="trnsfr" {{ isset($pay_method) && $pay_method == 'trnsfr' ? 'selected' : '' }}>Transfer to Bank Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label> Bank </label> 
                        <input name="bank" class="form-control" value="{{ $bank ?? '' }}" />
                    </div>
                    <div class="mb-3">
                        <label> Bank Account </label> 
                        <input name="bank_account" class="form-control" value="{{ $bank_account ?? '' }}" />
                    </div>
                    
                    <div class="mb-3">
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
@endsection