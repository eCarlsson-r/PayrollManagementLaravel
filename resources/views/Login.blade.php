@extends('guest')

@section('content')
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
                <p class="text-muted text-center btn-block btn btn-primary btn-rect">
                    Enter your username and password
                </p>
                <input type="email" id="email" name="email" placeholder="Username" class="form-control" />
                <input type="password" id="password" name="password" placeholder="Password" class="form-control" />
                <input name="login" class="btn text-muted text-center btn-danger" type="submit" value="Sign in">
            </form>
        </div>
        <div id="forgot" class="tab-pane">
            <form action="/forgot" method="post" class="form-signin">
                @csrf
                <p class="text-muted text-center btn-block btn btn-primary btn-rect">Enter your valid e-mail</p>
                <input type="email" name="email" required="required" placeholder="Your E-mail"  class="form-control" />
                <br />
                <button class="btn text-muted text-center btn-success" type="submit">Recover Password</button>
            </form>
        </div>
        <div id="register" class="tab-pane">
            <form action="/register" method="post" class="form-signin">
                @csrf
                <p class="text-muted text-center btn-block btn btn-primary btn-rect">
                    Enter details to signup
                </p>
                <div class="row">
                    <div class="col-sm-6">
                        <input type="text" name="first_name" placeholder="First Name" class="form-control" />
                    </div>
                    <div class="col-sm-6">
                        <input type="text" name="last_name" placeholder="Last Name" class="form-control" />
                    </div>
                </div>
                <select name="position" class="form-control">
                    <option value="SF">Staff</option>
                    <option value="MG">Manager</option>
                </select>
                <input type="email" name="email" placeholder="Username" class="form-control" />
                <input type="password" name="password" placeholder="Password" class="form-control" />
                <button type="submit" name="register" class="btn text-center btn-success">Sign Up</button>
            </form>
        </div>
    </div>
    <div class="text-center">
        <ul class="list-inline">
            <li><a class="text-muted" show="#login" data-toggle="tab">Login</a></li>
            <li><a class="text-muted" show="#forgot" data-toggle="tab">Forgot Password</a></li>
            <li><a class="text-muted" show="#register" data-toggle="tab">Signup</a></li>
        </ul>
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