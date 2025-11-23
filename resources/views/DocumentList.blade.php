@extends('member')

@section('title', 'Document Uploads')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<h2> Document Uploads </h2>
	</div> 
</div>
<hr />
<p>Here are document uploads you need to respond.</p>

@foreach ($documents as $document)
	<div class="card mb-3">
		<div class="card-header clearfix"> 
			<h4 class="btn card-title pull-left" data-bs-toggle="collapse" data-parent="#accordion" data-bs-target="#collapse{{ $loop->index }}">
				{{ $document->subject }} {{ $document->employee->first_name }} {{ $document->employee->last_name }}
			</h4>
			<h4 class="card-title pull-right">
				<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiModal{{ $loop->index }}"> Respond Upload </button>
			</h4>
		</div>
		<div id="collapse{{ $loop->index }}" class="card-collapse collapse in">
			<div class="card-body">
				<img src="{{ $document->file }}" height="300" />
			</div>
		</div>

		<div class="modal fade" id="uiModal{{ $loop->index }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog"> 
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="H3">Respond to Upload</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
					</div>
					<form action="/document/{{ $document->id }}" method="post" role="form">
						@csrf
						@method('PUT')
						<div class="modal-body">
							<div class="row">
								<div class="col-lg-6">
									<img class="img-fluid" src="{{ $document->file }}" />
								</div>
								<div class="col-lg-6">
									@if ($document->subject == "Time Card")
										<div class="mb-3">
											<label>Card ID</label> 
											<input class="form-control" name="id" />
										</div>
										<div class="mb-3">
											<label>Card Date</label>
											<input type="date" class="form-control" name="date" />
										</div>
										<div class="mb-3">
											<label>Start Time</label>
											<input type="time" class="form-control" name="time_start" />
										</div>
										<div class="mb-3">
											<label>End Time</label>
											<input type="time" class="form-control" name="time_end" />
										</div>
									@elseif ($document->subject == "Sales Receipt")
										<div class="mb-3">
											<label>Receipt ID</label>
											<input type="text" class="form-control" name="id" />
										</div>
										<div class="mb-3">
											<label>Receipt Date</label>
											<input type="date" class="form-control" name="date" />
										</div>
										<div class="mb-3">
											<label>Receipt Amount</label>
											<input type="number" class="form-control" name="amount" />
										</div>
									@endif
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save changes</button>
						</div> 
					</form>
				</div> 
			</div> 
		</div>
	</div>
@endforeach
@endsection