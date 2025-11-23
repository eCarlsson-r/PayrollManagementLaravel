@extends('member')

@section('title', 'Colleague')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<h2> Colleague </h2>
	</div>
</div>
<hr />
<div class="row">
	<div class="col-lg-12">
		<form action="/colleague" method="post">
			@csrf
			<div class="mb-3 row">
				<div class="col-xs-5 col-md-3">
					<label>Select an employee to view</label>
				</div>
				<div class="col-xs-7 col-md-9">
					<div class="input-group">
						<select name="person" data-placeholder="Colleagues" class="form-control">
							<option selected="selected">Please select one</option>
							@foreach ($employees as $friend)
								<option value="{{ $friend['id'] }}">{{ $friend['first_name'] }} {{ $friend['last_name'] }}</option>
							@endforeach
						</select>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="submit">
								<i class="fa fa-search"></i>
							</button>
						</span>
					</div>
				</div>
			</div>
		</form>

		@if ($viewColleague == "true")
		<div class="panel panel-default" >
			<div class="panel-heading"> Selected Employee Profile </div>
			<div class="panel-body">
				<div class="mb-3">
					<label> First Name </label>
					<input disabled class="form-control" value="{{ $colleague->first_name }}"/>
				</div>
				<div class="mb-3">
					<label> Last Name </label>
					<input disabled class="form-control" value="{{ $colleague->last_name }}"/>
				</div>
				<div class="mb-3">
					<label> Position-POS </label> 
					<select disabled class="form-control">
						<option value="SF" {{ $colleague->position == 'SF' ? 'selected' : '' }}>Staff</option>
						<option value="MG" {{ $colleague->position == 'MG' ? 'selected' : '' }}>Manager</option>
					</select>
				</div>
				<div class="mb-3"> 
					<label> Date of Birth </label>
					<input disabled class="form-control" value="{{ $colleague->dob }}"/>
				</div>
				<div class="mb-3">
					<label> Email </label>
					<input disabled class="form-control" value="{{ $colleague->email }}"/>
				</div> 
			</div>
		</div>
		@endif
	</div>
</div>
@endsection