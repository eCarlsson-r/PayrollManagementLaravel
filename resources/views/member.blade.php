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
        @vite(['resources/css/app.css', 'resources/css/main.css', 'resources/js/app.js'])
    </head>

    <body class="padTop53" >
        <div id="wrap">
            <div id="top">
                <nav class="navbar navbar-inverse navbar-fixed-top">
                    <a data-original-title="Show/Hide Menu" data-placement="bottom" data-tooltip="tooltip" class="accordion-toggle btn btn-primary btn-sm visible-xs" data-toggle="collapse" href="#menu" id="menu-toggle">
                        <i class="icon-align-justify"></i>
                    </a>
                    
                    <header class="navbar-header">
                        <a href="/employee/{{ Auth::user()->employee->id }}/edit" class="navbar-brand" style="padding-top: 5px;">
                            <img src="{{URL::asset('/logo.png')}}" width="" height="40" alt="" />
                        </a>
                    </header>
                </nav>
            </div>

            <div id="left">
                <div class="media user-media well-small">
                    <a class="user-link" href="#">
                        <img class="media-object img-thumbnail user-img" alt="User Picture" src="{{URL::asset('/images/user.jpg')}}" width="85" height="" />
                    </a>
                    <div class="media-body">
                        <h5 class="media-heading">{{ Auth::user()->employee->first_name }}</h5>
                        <ul class="list-unstyled user-info">
                            <li> 
                                <a style="width: 10px;height: 12px;"></a> POS: {{ Auth::user()->type }}
                            </li>
                        </ul>
                    </div>
                    <br />
                </div>

                <ul id="menu" class="collapse">
                    <li class="{{ (request()->path() == 'employee/'.Auth::user()->employee->id.'/edit') ? "panel active" : "panel" }}">
                        <a href="/employee/{{ Auth::user()->employee->id }}/edit"> 
                            <i class="icon-user"></i> Profile 
                        </a> 
                    </li>
                    <li class="{{ (request()->path() == 'colleague') ? "panel active" : "panel" }}">
                        <a href="/colleague">
                            <i class="icon-sitemap"></i> Colleagues
                        </a>
                    </li>
                    @if (Auth::user()->type=="Manager" || Auth::user()->type=="Admin")
                    <li class="{{ (request()->path() == 'team') ? "panel active" : "panel" }}">
                        <a href="/team/">
                            <i class="icon-group"></i> Manage Team
                        </a>
                    </li>
                    @endif

                    @if (Auth::user()->type == "Employee" || Auth::user()->type=="Manager")
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

                    @if (Auth::user()->type=="Manager" || Auth::user()->type=="Admin")
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
                    
                    @if (Auth::user()->type=="Admin")
                    <li class="{{ (request()->path() == 'employee') ? "panel active" : "panel" }}">
                        <a href="/employee">
                            <i class="icon-group"></i> Manage Employee
                        </a>
                    </li>

                    <li class="{{ (request()->path() == 'administration') ? "panel active" : "panel" }}">
                        <a href="#">
                            <i class="icon-lock"></i> Administration
                        </a> 
                    </li>
                    <li class="{{ (request()->path() == 'payment') ? "panel active" : "panel" }}"> 
                        <a href="#">
                            <i class="icon-usd"></i> Proceed Payment
                        </a> 
                    </li>
                    @endif
                    
                    <li class="{{ (request()->path() == 'payment') ? "panel active" : "panel" }}">
                        <a href="/payment">
                            <i class="icon-list"></i> Payment History
                        </a>
                    </li>

                    <li class="panel">
                        <a href="/logout">
                            <i class="icon-signout"></i> Logout 
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