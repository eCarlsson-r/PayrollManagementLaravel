@extends('member')

@section('title', 'Send Feedback')

@section('content')
<div id="content">
    <div class="inner">
        <div class="row">
            <div class="col-lg-12">
                <h2> Pending Requests </h2>
            </div>
        </div>
        <hr />   
        @foreach ($request as $pending)
        <div class="panel panel-default" >
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-12">
                        <h4 class="panel-title pull-left">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $pending->id }}">
                                {{ $pending->employee->first_name }} {{ $pending->employee->last_name }} : {{ $pending->subject }}
                            </a>
                        </h4>
                        <a class="btn btn-info pull-right" href="/respond/{{ $pending->id }}"> Respond Request </a>
                    </div>
                </div>
            </div>
            <div id="collapse{{ $pending->id }}" class="panel-collapse collapse">
                <div class="panel-body">
                    <embed src="/document/{{ $pending->id }}" width="100%" height="600"></embed>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection