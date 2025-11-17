<!DOCTYPE html>
<html>
    <!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
    <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
    <!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
    <head>
        <meta charset="UTF-8">
        <title>@yield('title', 'Payroll Management System')</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body>
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="navbar-header">
                @mobile
                @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                <button type="button" class="navbar-toggle" data-toggle="dropdown">
                    @if (count(auth()->user()->unreadNotifications) > 0)
                        <span class="badge">{{ count(auth()->user()->unreadNotifications) }}</span> <i class="fa fa-envelope"></i>&nbsp; <i class="fa fa-chevron-down"></i>
                    @else
                        <i class="fa fa-envelope"></i>&nbsp; <i class="fa fa-chevron-down"></i>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach (auth()->user()->unreadNotifications as $notification)
                        <li>
                            @if ($notification->data['type'] == "feedback")
                                <a href="/feedback/{{ $notification->data['id'] }}">
                            @else
                                <a href="/document/{{ $notification->data['id'] }}">
                            @endif
                                <div>
                                    <strong>{{ $notification->data['employee_name'] }}</strong>
                                </div>
                                <div>
                                    {{ $notification->data['title'] }}<br /> 
                                    @if ($notification->data['type'] == "feedback")
                                        <span class="label label-info">Feedback</span>
                                    @else
                                        <span class="label label-warning">Document</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                    @endforeach
                    <li>
                        <a class="text-center" href="/document">
                            <strong>Read All Messages</strong> <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
                @endif
                @endmobile

                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-navbar" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                @mobile
                <a href="/employee/{{ auth()->user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 8px;">
                    <img src="{{URL::asset('/logo.png')}}" width="" height="40" alt="" />
                </a>
                @elsemobile
                <a href="/employee/{{ auth()->user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 5px;">
                    <img src="{{URL::asset('/logo.png')}}" width="" height="40" alt="" />
                </a>
                @endmobile
            </div>

            <div class="collapse navbar-collapse" id="menu-navbar">
                <ul class="nav navbar-nav">
                    <li class="{{ (request()->path() == 'employee/'.auth()->user()->employee->id.'/edit') ? "active" : "" }}">
                        <a href="/employee/{{ auth()->user()->employee->id }}/edit"> 
                            <i class="fa fa-user"></i> Profile 
                        </a> 
                    </li>
                    <li class="{{ (request()->path() == 'colleague') ? "active" : "" }}">
                        <a href="/colleague">
                            <i class="fa fa-search"></i> Colleague
                        </a>
                    </li>
                    @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                    <li class="{{ (request()->path() == 'team') ? "active" : "" }}">
                        <a href="/team/">
                            <i class="fa fa-sitemap"></i> Manage Team
                        </a>
                    </li>
                    @endif

                    @if (auth()->user()->type == "Employee" || auth()->user()->type=="Manager")
                    <li>
                        <a href="/document/create">
                            <i class="fa fa-upload"></i> Upload File  
                        </a>
                    </li>
                    <li>
                        <a href="/feedback/create">
                            <i class="fa fa-edit"></i> Write Feedback  
                        </a>
                    </li>
                    @endif

                    @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                        <li class="{{ (request()->path() == 'feedback') ? "active" : "" }}">
                            <a href="/feedback">
                                <i class="fa fa-comments"></i> View Feedback
                            </a>
                        </li>

                        <li class="{{ (request()->path() == 'document') ? "active" : "" }}">
                            <a href="/document">
                                <i class="fa fa-upload"></i> Pending Requests
                            </a>
                        </li>
                    @endif
                    
                    @if (auth()->user()->type=="Admin")
                    <li class="{{ (request()->path() == 'employee') ? "active" : "" }}">
                        <a href="/employee">
                            <i class="fa fa-group"></i> Manage Employee
                        </a>
                    </li>

                    <li class="{{ (request()->path() == 'payment/create') ? "active" : "" }}">
                        <a href="/payment/create">
                            <i class="fa fa-usd"></i> Proceed Payment
                        </a>
                    </li>
                    @endif
            
                    <li class="{{ (request()->path() == 'payment') ? "active" : "" }}">
                        <a href="/payment">
                            <i class="fa fa-list"></i> Payment History
                        </a>
                    </li>

                    @notmobile
                    @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown">
                                @if (count(auth()->user()->notifications) > 0)
                                    <span class="badge">{{ count(auth()->user()->notifications) }}</span> <i class="fa fa-envelope"></i>&nbsp; <i class="fa fa-chevron-down"></i>
                                @else
                                    <i class="fa fa-envelope"></i>&nbsp; <i class="fa fa-chevron-down"></i>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @foreach (auth()->user()->notifications as $notification)
                                    <li>
                                        @if ($notification->data['type'] == "feedback")
                                            <a href="/feedback/{{ $notification->data['id'] }}">
                                        @else
                                            <a href="/document/{{ $notification->data['id'] }}">
                                        @endif
                                            <div>
                                                <strong>{{ $notification->data['employee_name'] }}</strong>
                                            </div>
                                            <div>
                                                {{ $notification->data['title'] }}<br /> 
                                                @if ($notification->data['type'] == "feedback")
                                                    <span class="label label-info">Feedback</span>
                                                @else
                                                    <span class="label label-warning">Document</span>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
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

                    <li>
                        <a href="/logout">
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
        </nav>
        
        <div class="container-fluid">
        @yield('content')
        </div>

        <nav class="navbar navbar-default navbar-fixed-bottom">
            <p class="navbar-text text-center">&copy; Carlsson Studio 2025</p>
        </nav>
        @yield('script')
    </body>
</html>