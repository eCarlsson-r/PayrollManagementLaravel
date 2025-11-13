@extends('member')

@section('title', 'File Uploads')

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
                            <select class="form-control" name="subject"/>
                                <option value="Time Card">Time Card</option>
                                <option value="Sales Receipt">Sales Receipt</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-xs-3">
                            <label>Upload File</label>
                        </div>
                        <div class="col-xs-9">
                            <input type="file" name="file" class="file" accept="image/png, image/jpeg" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" name="upload" class="btn btn-success">Upload Files</button>
                            <button type="reset" class="btn btn-danger">Reset Form</button>
                        </div>
                    </div>
                </form> 
            </div> 
        </div> 
    </div>
</div>
@endsection