@extends('member')

@section('title', 'Document Uploads')

@section('content')
<div id="content">
    <div class="inner">
        <div class="row">
			<div class="col-lg-12">
				<h2> Document Uploads </h2>
			</div> 
		</div>
        <hr />
        <p>Here are document uploads you need to respond.</p>

        @foreach ($documents as $document)
			<div class="panel panel-default">
				<div class="panel-heading clearfix"> 
					<h4 class="btn panel-title pull-left">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $loop->index }}">
							{{ $document->subject }} {{ $document->employee->first_name }} {{ $document->employee->last_name }}
						</a>
					</h4>
					<h4 class="panel-title pull-right">
						<button class="btn btn-primary" data-toggle="modal" data-target="#uiModal{{ $loop->index }}"> Respond Upload </button>
					</h4>
				</div>
				<div id="collapse{{ $loop->index }}" class="panel-collapse collapse in">
					<div class="panel-body">
						<img src="{{ $document->file_path }}" height="300" />
					</div>
				</div>

				<div class="modal fade" id="uiModal{{ $loop->index }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog"> 
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="H3">Respond to Upload</h4>
							</div>
							<form action="/document/{{ $document->id }}" method="post" role="form">
								@csrf
								@method('PUT')
								<div class="modal-body">
									<div class="row">
										<div class="col-lg-6">
											<img class="img-responsive" src="{{ $document->file_path }}" />
										</div>
										<div class="col-lg-6">
											@if ($document->subject == "Time Card")
												<div class="form-group">
													<label>Card ID</label> 
													<input class="form-control" name="id" />
												</div>
												<div class="form-group">
													<label>Card Date</label>
													<input type="date" class="form-control" name="date" />
												</div>
												<div class="form-group">
													<label>Start Time</label>
													<input type="time" class="form-control" name="time_start" />
												</div>
												<div class="form-group">
													<label>End Time</label>
													<input type="time" class="form-control" name="time_end" />
												</div>
											@elseif ($document->subject == "Sales Receipt")
												<div class="form-group">
													<label>Receipt ID</label>
													<input type="text" class="form-control" name="id" />
												</div>
												<div class="form-group">
													<label>Receipt Date</label>
													<input type="date" class="form-control" name="date" />
												</div>
												<div class="form-group">
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
	</div>
</div>
@endsection