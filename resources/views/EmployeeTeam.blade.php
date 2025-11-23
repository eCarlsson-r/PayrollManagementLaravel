@extends('member')

@section('title', 'Team Management')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <h2>Team Management</h2>
        <hr />
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <form method="post">
            @csrf
            <div class="row">
                <div class="col-lg-5">
                    <div class="mb-3"> 
                        <div class="input-group">
                            <input id="box1Filter" type="text" placeholder="Filter" class="form-control" />
                            <span class="input-group-btn">
                                <button id="box1Clear" class="btn btn-warning" type="button">
                                    <i class="fa fa-times"></i> 
                                </button> 
                            </span>
                        </div> 
                    </div>
                    <div class="mb-3">
                        <select id="box1View" name="new_employee[]" multiple="multiple" class="form-control" size="16">
                            @foreach ($new_members as $member)
                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select> 
                    </div> 
                </div>
                <div class="col-lg-2 text-center">
                    @mobile
                    <div class="mb-3 btn-group btn-group-justified">
                        <div class="btn-group" role="group">
                            <button name="addTeam1" type="submit" class="btn btn-primary" formaction="/recruit"> 
                                <i class="fa fa-chevron-down"></i> 
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button name="addTeam2" type="submit" class="btn btn-primary" formaction="/recruit"> 
                                <i class="fa fa-forward"></i> 
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button name="removeTeam2" type="submit" class="btn btn-danger" formaction="/expel"> 
                                <i class="fa fa-backward"></i> 
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <button name="removeTeam1" type="submit" class="btn btn-danger" formaction="/expel"> 
                                <i class="fa fa-chevron-up"></i> 
                            </button>
                        </div>
                    </div> 
                    @elsemobile
                    <div class="btn-group btn-group-vertical">
                        <button name="addTeam1" type="submit" class="btn btn-lg btn-primary" formaction="/recruit"> 
                            <i class="fa fa-chevron-right"></i> 
                        </button>
                        <button name="addTeam2" type="submit" class="btn btn-lg btn-primary" formaction="/recruit"> 
                            <i class="fa fa-forward"></i> 
                        </button>
                        <button name="removeTeam2" type="submit" class="btn btn-lg btn-danger" formaction="/expel"> 
                            <i class="fa fa-backward"></i> 
                        </button>
                        <button name="removeTeam1" type="submit" class="btn btn-lg btn-danger" formaction="/expel"> 
                            <i class="fa fa-chevron-left"></i> 
                        </button>
                    </div> 
                    @endmobile
                </div>
                <div class="col-lg-5">
                    <div class="mb-3">
                        <div class="input-group">
                            <input id="box2Filter" type="text" placeholder="Filter" class="form-control" />
                            <span class="input-group-btn"> 
                                <button id="box2Clear" class="btn btn-warning" type="button">
                                    <i class="fa fa-times"></i> 
                                </button>
                            </span>
                        </div> 
                    </div>
                    <div class="mb-3">
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
@endsection