<!DOCTYPE html>
<html>
    <!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
    <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
    <!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
    <head>
        <meta charset="UTF-8">
        <title>@yield('title', 'Payroll Management System')</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />

        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        @vite(['resources/css/app.css', 'resources/css/MoneAdmin.css', 'resources/css/main.css', 'resources/js/app.js'])
    </head>

    <body class="padTop53" >
        <div id="wrap">
            <div id="top">
                <nav class="navbar navbar-inverse navbar-fixed-top">
                    <a data-original-title="Show/Hide Menu" data-placement="bottom" data-tooltip="tooltip" class="accordion-toggle btn btn-primary btn-sm visible-xs" data-toggle="collapse" href="#menu" id="menu-toggle">
                        <i class="icon-align-justify"></i>
                    </a>
                    
                    <header class="navbar-header">
                        <a href="/employee/{{ auth()->user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 5px;">
                            <img src="{{URL::asset('/logo.png')}}" width="" height="40" alt="" />
                        </a>
                    </header>

                    <ul class="nav navbar-top-links navbar-right">
                        @if (auth()->user()->type=="Admin")
                            <li class="dropdown">
                                <a href="/payment/create">
                                    <i class="icon-usd"></i>
                                </a>
                            </li>
                        @endif

                        @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                    @if (count(auth()->user()->notifications) > 0)
                                        <span class="label label-success">{{ count(auth()->user()->notifications) }}</span> <i class="icon-envelope-alt"></i>&nbsp; <i class="icon-chevron-down"></i>
                                    @else
                                        <span class="label label-success"></span> <i class="icon-envelope-alt"></i>&nbsp; <i class="icon-chevron-down"></i>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-messages">
                                    @foreach (auth()->user()->notifications as $notification)
                                        <li>
                                            <a href="#">
                                                <div>
                                                    <strong>{{ $notification->data['employee_name'] }}</strong>
                                                </div>
                                                <div>
                                                    {{ $notification->data['title'] }}<br /> 
                                                    <span class="label label-primary">Feedback</span> 
                                                </div>
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                    @endforeach
                                    <li>
                                        <a class="text-center" href="/document">
                                            <strong>Read All Messages</strong> <i class="icon-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <li class="dropdown">
                            <a href="/logout">
                                <i class="icon-signout"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div id="left">
                <div class="media user-media well-small">
                    <a class="user-link" href="#">
                        <img class="media-object img-thumbnail user-img" alt="User Picture" src="{{URL::asset('/images/user.jpg')}}" width="85" height="" />
                    </a>
                    <div class="media-body">
                        <h5 class="media-heading">{{ auth()->user()->employee->first_name }}</h5>
                        <ul class="list-unstyled user-info">
                            <li> 
                                <a style="width: 10px;height: 12px;"></a> POS: {{ auth()->user()->type }}
                            </li>
                        </ul>
                    </div>
                    <br />
                </div>

                <ul id="menu" class="collapse">
                    <li class="{{ (request()->path() == 'employee/'.auth()->user()->employee->id.'/edit') ? "panel active" : "panel" }}">
                        <a href="/employee/{{ auth()->user()->employee->id }}/edit"> 
                            <i class="icon-user"></i> Profile 
                        </a> 
                    </li>
                    <li class="{{ (request()->path() == 'colleague') ? "panel active" : "panel" }}">
                        <a href="/colleague">
                            <i class="icon-sitemap"></i> Colleagues
                        </a>
                    </li>
                    @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                    <li class="{{ (request()->path() == 'team') ? "panel active" : "panel" }}">
                        <a href="/team/">
                            <i class="icon-group"></i> Manage Team
                        </a>
                    </li>
                    @endif

                    @if (auth()->user()->type == "Employee" || auth()->user()->type=="Manager")
                    <li class="panel" class="panel">
                        <a href="#" data-parent="#menu" data-toggle="collapse" class="accordion-toggle" data-target="#blank-nav">
                            <i class="icon-pencil"></i> Requests
                            <span class="pull-right"> <i class="icon-angle-right"></i> </span>&nbsp; 
                            <span class="label label-success"></span>&nbsp; 
                        </a>
                        <ul class="collapse" id="blank-nav">
                            <li>
                                <a href="/document/create">
                                    <i class="icon-upload"></i> Post File  
                                </a>
                            </li>
                            <li>
                                <a href="/feedback/create">
                                    <i class="icon-edit"></i> Feedback  
                                </a>
                            </li>
                        </ul> 
                    </li>
                    @endif

                    @if (auth()->user()->type=="Manager" || auth()->user()->type=="Admin")
                        <li class="{{ (request()->path() == 'feedback') ? "panel active" : "panel" }}">
                            <a href="/feedback" >
                                <i class="icon-comments-alt"></i> View Feedback
                            </a>
                        </li>

                        <li class="{{ (request()->path() == 'request') ? "panel active" : "panel" }}">
                            <a href="/document">
                                <i class="icon-question-sign"></i> Pending Requests
                            </a>
                        </li>
                    @endif
                    
                    @if (auth()->user()->type=="Admin")
                    <li class="{{ (request()->path() == 'employee') ? "panel active" : "panel" }}">
                        <a href="/employee">
                            <i class="icon-group"></i> Manage Employee
                        </a>
                    </li>
                    @endif
                    
                    <li class="{{ (request()->path() == 'payment') ? "panel active" : "panel" }}">
                        <a href="/payment">
                            <i class="icon-list"></i> Payment History
                        </a>
                    </li>
                </ul>
            </div>
            
            @yield('content')

            <div id="footer">
                <p>&copy; Enterprise Software Developer &nbsp;2015 &nbsp;</p>
            </div>
        </div>
    </body>
</html>