<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\Career;

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
            'new_members' => Employee::where('manager', '')->where('id', '!=', 'admin')->where('id', '!=', auth()->user()->employee->id)->get(),
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
        if ($request->input('position') == 'Employee') $id = sprintf("SF%06d", $empID+1);
        else if ($request->input('position') == 'Manager') $id = sprintf("MG%06d", $empID+1);
        $empData = array(
            'id' => $id
        );
        $empData = array_merge(
            $empData, $request->only(
                [
                    'first_name', 'last_name', 'position', 'dob', 
                    'email', 'contact', 'address', 'pay_method'
                ]
            )
        );
        if ($request->input('pay_method') == "trnsfr") {
            $empData = array_merge($empData, $request->only(['bank', 'bank_account']));
        }

        $employee = Employee::create($empData);

        $schemaData = [
            'scheme' => $request->input('scheme'),
            'employee_id' => $employee->id
        ];
        
        if ($request->input('scheme') == "HOURLY" || $request->input('scheme') == "MONTHLY") {
            $schemaData['base_amount'] = $request->input('base_amount');
        } else if ($request->input('scheme') == "COMMISSION") {
            $schemaData['base_amount'] = $request->input('base_amount');
            $schemaData['base_commision_rate'] = $request->input('base_commision_rate');
        }
        Scheme::create($schemaData);
        Career::create(['employee_id' => $employee->id, 'position' => $request->input('position'), 'start_date' => now()]);
        return redirect('employee');
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('Profile', Employee::with('career')->find($id));
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
                        'email', 'contact', 'address', 
                        'pay_method', 'bank', 'bank_account'
                    ]
                ),
                function ($value) {
                    return $value !== null;
                }
            )
        );

        if ($request->input('password') !== "" && $request->input('password') == $request->input('password_confirmation')) {
            $account = auth()->user();
            $account->password = Hash::make($request->input('password'));
            $account->save();
            event(new PasswordReset($account));
        }

        return back();
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
