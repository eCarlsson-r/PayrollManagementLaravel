@extends('guest')

@section('content')
<div class="mb-3">
    <form action="/password/reset" method="post" class="form-signin">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <input type="hidden" name="token" value="{{ $token }}" />
        <div class="panel panel-primary">
            <div class="panel-heading">
                Enter your new password and confirm
            </div>
            <div class="panel-body">
                <div class="mb-3">
                    <label for="email">E-mail address</label>
                    <input type="email" name="email" class="form-control" value={{ $email }} />
                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" />
                </div>
                <div class="mb-3 text-center">
                    <input name="login" class="btn btn-primary" type="submit" value="Reset Password">
                </div>
            </div>
        </div>
    </form>
</div>
@endsection