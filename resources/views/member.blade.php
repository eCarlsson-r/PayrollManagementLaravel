<!DOCTYPE html>
<html>
    <!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
    <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
    <!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
    <head>
        @PwaHead <!-- Add this directive to include the PWA meta tags -->
        <meta charset="UTF-8">
        <title>@yield('title', 'Payroll Management System')</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body>
        <nav class="navbar fixed-top navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                @mobile
                <a href="/employee/{{ auth()->user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 8px;">
                    <img src="{{URL::asset('/banner.png')}}" width="" height="40" alt="" />
                </a>

                @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                <button class="navbar-toggler" type="button" data-bs-toggle="dropdown">
                    @if (count(auth()->user()->unreadNotifications) > 0)
                        <i class="fa fa-envelope"></i> <span class="badge bg-secondary">{{ count(auth()->user()->unreadNotifications) }}</span>
                    @else
                        <i class="fa fa-envelope"></i>
                    @endif
                </button>
                <ul class="dropdown-menu">
                    @foreach (auth()->user()->unreadNotifications as $notification)
                        <li>
                            @if ($notification->type == "App\Notifications\FeedbackSent")
                                <a class="dropdown-item" href="/feedback/{{ $notification->data['id'] }}">
                            @else
                                <a class="dropdown-item" href="/document/{{ $notification->data['id'] }}">
                            @endif
                                <div>
                                    <strong>{{ $notification->data['employee_name'] }}</strong>
                                </div>
                                <div>
                                    {{ $notification->data['title'] }}<br /> 
                                    @if ($notification->type == "App\Notifications\FeedbackSent")
                                        <span class="label label-info">Feedback</span>
                                    @else
                                        <span class="label label-warning">Document</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endforeach
                    <li>
                        <a class="text-center" href="/document">
                            <strong>Read All Messages</strong> <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
                @endif

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-navbar" aria-controls="menu-navbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                @elsemobile
                <a href="/employee/{{ auth()->user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 5px;">
                    <img src="{{URL::asset('/banner.png')}}" width="" height="40" alt="" />
                </a>
                @endmobile
                <div class="collapse navbar-collapse" id="menu-navbar">
                    <ul class="nav navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'employee/'.auth()->user()->employee->id.'/edit') ? "active" : "" }}" href="/employee/{{ auth()->user()->employee->id }}/edit"> 
                                <i class="fa fa-user"></i> Profile 
                            </a> 
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'colleague') ? "active" : "" }}" href="/colleague">
                                <i class="fa fa-search"></i> Colleague
                            </a>
                        </li>
                        @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'team') ? "active" : "" }}" href="/team/">
                                <i class="fa fa-sitemap"></i> Manage Team
                            </a>
                        </li>
                        @endif

                        @if (auth()->user()->type == "Employee" || auth()->user()->type=="Manager")
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'document/create') ? "active" : "" }}" href="/document/create">
                                <i class="fa fa-upload"></i> Upload File  
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'feedback/create') ? "active" : "" }}" href="/feedback/create">
                                <i class="fa fa-edit"></i> Write Feedback  
                            </a>
                        </li>
                        @endif

                        @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->path() == 'feedback') ? "active" : "" }}" href="/feedback">
                                    <i class="fa fa-comments"></i> View Feedback
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ (request()->path() == 'document') ? "active" : "" }}" href="/document">
                                    <i class="fa fa-upload"></i> Pending Requests
                                </a>
                            </li>
                        @endif
                        
                        @if (auth()->user()->type=="Admin")
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'employee') ? "active" : "" }}" href="/employee">
                                <i class="fa fa-group"></i> Manage Employee
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'payment/create') ? "active" : "" }}" href="/payment/create">
                                <i class="fa fa-usd"></i> Proceed Payment
                            </a>
                        </li>
                        @endif
                
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->path() == 'payment') ? "active" : "" }}" href="/payment">
                                <i class="fa fa-list"></i> Payment History
                            </a>
                        </li>

                        @notmobile
                        @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @if (count(auth()->user()->unreadNotifications) > 0)
                                        <span class="badge bg-secondary">{{ count(auth()->user()->unreadNotifications) }}</span> <i class="fa fa-envelope"></i>
                                    @else
                                        <i class="fa fa-envelope"></i>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    @foreach (auth()->user()->unreadNotifications as $notification)
                                        <li>
                                            @if ($notification->type == "App\Notifications\FeedbackSent")
                                                <a class="dropdown-item" href="/feedback/{{ $notification->id }}">
                                            @else
                                                <a class="dropdown-item" href="/document/{{ $notification->id }}">
                                            @endif
                                                <div>
                                                    <strong>{{ $notification->data['employee_name'] }}</strong>
                                                </div>
                                                <div>
                                                    {{ $notification->data['title'] }}<br /> 
                                                    @if ($notification->type == "App\Notifications\FeedbackSent")
                                                        <span class="label label-info">Feedback</span>
                                                    @else
                                                        <span class="label label-warning">Document</span>
                                                    @endif
                                                </div>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                    <li>
                                        <a class="text-center" href="/document">
                                            <strong>Read All Messages</strong> <i class="fa fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @endnotmobile

                        <li class="nav-item">
                            <a class="nav-link" href="/logout">
                                @notmobile
                                @if (auth()->user()->type=="Admin")
                                    <i class="fa fa-sign-out"></i>
                                @else
                                    <i class="fa fa-sign-out"></i> Log Out
                                @endif
                                @elsenotmobile
                                <i class="fa fa-sign-out"></i> Log Out
                                @endnotmobile
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="content container-fluid">
        @yield('content')
        </div>

        <nav class="navbar fixed-bottom bg-body-tertiary justify-content-center">
            <span class="navbar-text">&copy; Carlsson Studio 2025</span>
        </nav>

        <script>
            window.userId = {{ auth()->user()->id }};
            window.publicKey = '{{ config('webpush.vapid.public_key') }}';
        </script>
        @vite(['resources/js/echo.js'])
    </body>
</html>