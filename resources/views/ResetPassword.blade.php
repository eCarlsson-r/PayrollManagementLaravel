@extends('guest')

@section('content')
    <form action="/password/reset" method="post" class="form-signin">
        @csrf
        @if (session('error') && session('message'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
        @endif
        
        @if (session('status'))
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('status') }}
            </div>
        @elseif (session('error') && session('email'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('email') }}
            </div>
        @endif
        <input type="hidden" name="token" value="{{ $token }}" />
        <p class="text-muted text-center btn-block btn btn-primary btn-rect">
            Enter your new password and confirm
        </p>
        <input type="email" name="email" placeholder="Username" class="form-control" value={{ $email }} />
        <input type="password" name="password" placeholder="Password" class="form-control" />
        <input type="password" name="password_confirmation" placeholder="Password" class="form-control" />
        <input name="login" class="btn text-muted text-center btn-danger" type="submit" value="Sign in">
    </form>
@endsection