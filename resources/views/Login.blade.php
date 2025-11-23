@extends('guest')

@section('content')
<div class="mb-3">
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" data-bs-target="#login" data-bs-toggle="tab">Login</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-target="#forgot" data-bs-toggle="tab">Forgot Password</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-target="#register" data-bs-toggle="tab">Signup</a></li>
    </ul>
    <div class="tab-content">
        <div id="login" class="tab-pane active">
            <form action="/login" method="post" class="form-signin">
                @csrf
                @if (session('error') && session('message'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if (session('status'))
                    <div class="alert alert-info alert-dismissible" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @elseif (session('error') && session('email'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        {{ session('email') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div class="card card-default">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="login-email">E-mail address</label>
                            <input type="email" id="login-email" name="email" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" />
                        </div>
                        <div class="mb-3 text-center">
                            <input name="login" class="btn btn-primary" type="submit" value="Sign in">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="forgot" class="tab-pane">
            <form action="/forgot" method="post" class="form-signin">
                @csrf
                <div class="card card-default">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="forgot-email">E-mail address</label>
                            <input type="email" id="forgot-email" name="email" required="required" class="form-control" />
                        </div>
                        <div class="mb-3 text-center">
                            <button class="btn btn-primary" type="submit">Recover Password</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="register" class="tab-pane">
            <form action="/register" method="post" class="form-signin">
                @csrf
                <div class="card card-default">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="register-email">E-mail address</label>
                            <input type="email" name="email" id="register-email" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="register-password">Password</label>
                            <input type="password" name="password" id="register-password" class="form-control" />
                        </div>
                        <div class="mb-3 text-center">
                            <button type="submit" name="register" class="btn btn-primary">Sign Up</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection