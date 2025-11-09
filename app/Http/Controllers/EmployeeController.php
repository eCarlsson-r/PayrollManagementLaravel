<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('EmployeeList', ['employees'=> Employee::where('id', '!=', auth()->user()->employee->id)->get()]);
    }

    public function show(Request $request)
    {
        $person = $request->input('person');
        $returnData = ['viewColleague'=>'false', 'employees'=> Employee::where('id', '!=', 'admin')->where('id', '!=', auth()->user()->employee->id)->where('id', '!=', 1)->get()];
        if (isset($person)) {
            $colleague = Employee::where('id', $person)->first();
            $returnData['viewColleague'] = 'true';
            $returnData = array_merge($returnData, ['colleague' => $colleague]);
        }
        return view('Colleague', $returnData);
    }

    public function team()
    {
        return view('EmployeeTeam', [
            'new_members' => Employee::where('manager', "")->where('id', '!=', 'admin')->where('id', '!=', auth()->user()->employee->id)->get(),
            'team_members' => Employee::where('manager', auth()->user()->employee->id)->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('EmployeeForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $empID = Employee::where('position', $request->input('position'))->count();
        $empData = array(
            'id' => $request->input('position').sprintf("%06d", $empID+1),
            'bank' => '',
            'bank_account' => '',
            'manager' => 0
        );
        $empData = array_merge(
            $empData, $request->only(
                [
                    'first_name', 'last_name', 'position', 'dob', 
                    'email', 'contact', 'address', 'qualification', 
                    'career', 'pay_method'
                ]
            )
        );
        if ($request->input('pay_method') == "trnsfr") {
            $empData = array_merge($empData, $request->only(['bank', 'bank_account']));
        }
        return Employee::create($empData);
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('Profile', Employee::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        Employee::find($id)->update(
            array_filter(
                $request->only(
                    [
                        'id', 'first_name', 'last_name', 'position', 'dob', 
                        'email', 'contact', 'address', 'qualification', 'career', 
                        'pay_method', 'bank', 'bank_account'
                    ]
                ),
                function ($value) {
                    return $value !== null;
                }
            )
        );

        return redirect('profile');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Employee::destroy($request->input('employee'));
        return redirect('employee');
    }

    public function recruit(Request $request)
    {
        if ($request->input('new_employee')) {
            Employee::whereIn('id', $request->input('new_employee'))->update(['manager' => auth()->user()->employee->id]);
        } else {
            Employee::where('manager','')->where('id', auth()->user()->employee->id)->update(['manager' => auth()->user()->employee->id]);
        }
        return back();
    }

    public function expel(Request $request)
    {
        if ($request->input('team_member')) {
            Employee::whereIn('id', $request->input('team_member'))->update(['manager' => '']);
        } else {
            Employee::where('manager', auth()->user()->employee->id)->update(['manager' => '']);
        }
        return back();
    }
}
