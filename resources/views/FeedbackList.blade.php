@extends('member')

@section('title', 'Manage Feedback')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h2> Manage Feedback </h2> 
    </div> 
</div>
<hr />
<p>Here are feedbacks sent by your employees to you.</p>
@foreach ($feedbacks as $feedback)
    @if ($feedback->read == "U")
        <div class="card mb-3" >
            <div class="card-header" >
                <h4 class="card-title">
                    <a href="/read/{{ $feedback->id }}">
                        <span data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapse{{ $feedback->id }}">
                            From: {{ $feedback->employee->first_name }} {{ $feedback->employee->last_name }} - {{ $feedback->date }} {{ $feedback->time }}
                        </span>
                    </a>
                </h4>
            </div>
            <div id="collapse{{ $feedback->id }}" class="card-collapse collapse">
                <div class="card-body">
                    {{ $feedback->feedback; }}
                </div>
            </div>
        </div>
    @else
        <div class="card mb-3" >
            <div class="card-header" > 
                <h4 class="card-title"> 
                    <span data-bs-toggle="collapse" data-bs-parent="#accordion" data-bs-target="#collapse{{ $feedback->id }}">
                        From: {{ $feedback->employee->first_name }} {{ $feedback->employee->last_name }} - {{ $feedback->date }} {{ $feedback->time }}
                    </span>
                </h4> 
            </div>
            <div id="collapse{{ $feedback->id }}" class="card-collapse collapse show">
                <div class="card-body">
                    {{ $feedback->feedback; }}
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection