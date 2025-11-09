@extends('member')

@section('title', 'Send Feedback')

@section('content')
<div id="content">
    <div class="inner">
        <div class="row">
            <div class="col-lg-12">
                <h2> File Uploads </h2>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-12">
                <form action="/document" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <div class="col-xs-3">
                            <label>Subject of Upload </label>
                        </div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="subject"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-3">
                            <label>Upload File</label>
                        </div>
                        <div class="col-xs-9">
                            <div class="fileupload fileupload-new" data-provides="fileupload">
                                <div class="form-group">
                                    <i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span>
                                    <span class="btn btn-file btn-info">
                                        <span class="fileupload-new">Select file</span>
                                        <span class="fileupload-exists">Change</span>
                                        <input type="file" name="file"/>                                                    
                                    </span>
                                    <a class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" name="upload" class="btn btn-success"nnn>Upload Files</button>
                            <button type="reset" class="btn btn-danger">Reset Form</button>
                        </div>
                    </div>
                </form> 
            </div> 
        </div> 
    </div>
</div>
@endsection