@extends('member')

@section('title', 'Send Feedback')

@section('content')
<div class="row"> 
    <div class="col-lg-12"> 
        <h2> Send Feedback </h2> 
    </div> 
</div>
<hr />
<form action="/feedback" method="post">
    @csrf
    <label>Title of feedback: </label> 
    <input class="form-control" name="title" /> 
    <p> </p>
    <p>Write down your feedbacks you wish to tell your manager here.</p>
    <textarea id="wysihtml5" class="form-control" rows="10" name="feedback"></textarea>
    <div class="form-actions"> <br />
        <input type="submit" name="send" value="Send Feedback" class="btn btn-primary" />
    </div> 
</form> 
@endsection