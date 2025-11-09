@extends('member')

@section('title', 'Team Management')

@section('content')
<div id="content">
    <div class="inner" >
        <div class="row">
            <div class="col-lg-12">
                <h2> Team Management </h2>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <header>
                        <h5><i class="icon-th-large"></i> Manage your Team </h5>    
                        <div class="toolbar">
                            <ul class="nav pull-right">
                                <li> 
                                    <a class="accordion-toggle minimize-box" data-toggle="collapse" href="#div-3">
                                        <i class="icon-chevron-up"></i> 
                                    </a> 
                                </li>
                            </ul> 
                        </div> 
                    </header>
                    <div id="div-3" class="accordion-body collapse in body">
                        <form method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-5">
                                    <div class="form-group"> 
                                        <div class="input-group">
                                            <input id="box1Filter" type="text" placeholder="Filter" class="form-control" />
                                            <span class="input-group-btn">
                                                <button id="box1Clear" class="btn btn-warning" type="button">x</button> 
                                            </span>
                                        </div> 
                                    </div>
                                    <div class="form-group">
                                        <select id="box1View" name="new_employee[]" multiple="multiple" class="form-control" size="16">
                                            @foreach ($new_members as $member)
                                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                                            @endforeach
                                        </select> 
                                    </div> 
                                </div>
                                <div class="col-lg-2">
                                    <div class="btn-group btn-group-vertical" style="white-space: normal;">
                                        <button name="addTeam1" type="submit" class="btn btn-primary" formaction="/recruit"> 
                                            <i class="icon-chevron-right"></i> 
                                        </button>
                                        <button name="addTeam2" type="submit" class="btn btn-primary" formaction="/recruit"> 
                                            <i class="icon-forward"></i> 
                                        </button>
                                        <button name="removeTeam2" type="submit" class="btn btn-danger" formaction="/expel"> 
                                            <i class="icon-backward"></i> 
                                        </button>
                                        <button name="removeTeam1" type="submit" class="btn btn-danger" formaction="/expel"> 
                                            <i class="icon-chevron-left icon-white"></i> 
                                        </button>
                                    </div> 
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="box2Filter" type="text" placeholder="Filter" class="form-control" />
                                            <span class="input-group-btn"> 
                                                <button id="box2Clear" class="btn btn-warning" type="button">x</button>
                                            </span>
                                        </div> 
                                    </div>
                                    <div class="form-group">
                                        <select id="box2View" name="team_member[]" multiple="multiple" class="form-control" size="16">
                                            @foreach ($team_members as $member)
                                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> 
                            </div> 
                        </form>
                    </div> 
                </div> 
            </div> 
        </div> 
    </div>
</div> 
@endsection