@extends('member')

@section('title', 'File Uploads')

@section('content')
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
            <div class="mb-3 row">
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
            <div class="mb-3 row">
                <div class="col-xs-3">
                    <label>Upload File</label>
                </div>
                <div class="col-xs-9">
                    <input type="file" name="file" class="file" data-show-remove="false" data-show-upload="false" accept="image/png, image/jpeg" />
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
@endsection