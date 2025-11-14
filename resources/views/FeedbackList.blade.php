@extends('member')

@section('title', 'Manage Feedback')

@section('content')
<div id="content">
    <div class="inner">
        <div class="row">
            <div class="col-lg-12">
                <h2> Manage Feedback </h2> 
            </div> 
        </div>
        <hr />
        <p>Here are feedbacks sent by your employees to you.</p>
        @foreach ($feedbacks as $feedback)
            @if ($feedback->read == "U")
                <div class="panel panel-default" >
                    <div class="panel-heading" >
                        <h4 class="panel-title">
                            <strong>
                                <a data-toggle="collapse" data-parent="#accordion" data-target="#collapse{{ $feedback->id }}" href="/read/{{ $feedback->id }}">
                                    From: {{ $feedback->employee->first_name }} {{ $feedback->employee->last_name }} - {{ $feedback->date }} {{ $feedback->time }}
                                </a>
                            </strong>
                        </h4>
                    </div>
                    <div id="collapse{{ $feedback->id }}" class="panel-collapse collapse">
                        <div class="panel-body">
                            {{ $feedback->feedback; }}
                        </div>
                    </div>
                </div>
            @else
                <div class="panel panel-default" >
                    <div class="panel-heading" > 
                        <h4 class="panel-title"> 
                            <a data-toggle="collapse" data-parent="#accordion" data-target="#collapse{{ $feedback->id }}" href="/read/{{ $feedback->id }}">
                                From: {{ $feedback->employee->first_name }} {{ $feedback->employee->last_name }} - {{ $feedback->date }} {{ $feedback->time }}
                            </a>
                        </h4> 
                    </div>
                    <div id="collapse{{ $feedback->id }}" class="panel-collapse collapse in">
                        <div class="panel-body">
                            {{ $feedback->feedback; }}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection