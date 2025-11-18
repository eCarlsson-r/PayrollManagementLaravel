@extends('guest')

@section('content')
<div class="form-group">
    <ul class="nav nav-tabs">
        <li role="presentation" class="active"><a href="#login" data-toggle="tab">Login</a></li>
        <li role="presentation"><a href="#forgot" data-toggle="tab">Forgot Password</a></li>
        <li role="presentation"><a href="#register" data-toggle="tab">Signup</a></li>
    </ul>
    <div class="tab-content">
        <div id="login" class="tab-pane active">
            <form action="/login" method="post" class="form-signin">
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
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="login-email">E-mail address</label>
                            <input type="email" id="login-email" name="email" placeholder="Username" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Password" class="form-control" />
                        </div>
                        <div class="form-group text-center">
                            <input name="login" class="btn btn-primary" type="submit" value="Sign in">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="forgot" class="tab-pane">
            <form action="/forgot" method="post" class="form-signin">
                @csrf
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="forgot-email">E-mail address</label>
                            <input type="email" id="forgot-email" name="email" required="required" class="form-control" />
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary" type="submit">Recover Password</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="register" class="tab-pane">
            <form action="/register" method="post" class="form-signin">
                @csrf
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="register-email">E-mail address</label>
                            <input type="email" name="email" id="register-email" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="register-password">Password</label>
                            <input type="password" name="password" id="register-password" class="form-control" />
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" name="register" class="btn btn-primary">Sign Up</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.querySelectorAll('.list-inline li > a').forEach(button => {
        button.addEventListener('click', function () {
            var activeForm = this.getAttribute('show') + ' > form';
            document.querySelectorAll(".tab-pane").forEach(tab => tab.classList.remove('active'));
            document.querySelector(this.getAttribute('show')).classList.add('active');
            document.querySelector(activeForm).classList.add('magictime', 'swap');
            //set timer to 1 seconds, after that, unload the magic animation
            setTimeout(function () {
                document.querySelector(activeForm).classList.remove('magictime', 'swap');
            }, 1000);
        });
    });
</script>
@endsection