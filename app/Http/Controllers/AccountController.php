<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Employee;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'endpoint'    => ['required'],
            'keys.auth'   => ['required'],
            'keys.p256dh' => ['required']
        ]);
        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = auth()->user();
        $user->updatePushSubscription($endpoint, $key, $token);
        
        return response()->json(['success' => true],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $token)
    {
        return view("ResetPassword", ['token'=>$token, 'email'=>$request->email]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Employee::where(['email'=>$request->input('email')])->first()) {
            $employee = Employee::where(['email'=>$request->input('email')])->first();
            $empID = $employee->id;
            Account::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'type' => $employee->position,
                'employee_id' => $empID
            ]);
            
            if (auth()->attempt($request->only('email', 'password'))) return redirect()->intended('/employee/'.$empID.'/edit');
        } else {
            return back()->with(["error"=>true, "message"=>"Employee with this email does not exist."]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request) 
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        $user = Account::where('email', $request->email)->first();

		if (auth()->attempt($credentials)) {
            return redirect()->intended('/employee/'.auth()->user()->employee->id.'/edit');
		} else if (!$user) {
            return back()->with(["error"=>true, "message"=>"Account not found."]);
        } else if (!Hash::check($request->password, $user->password)) {
            return back()->with(["error"=>true, "message"=>"Password is wrong."]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Account $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return redirect('/')->with('status', __($status));
        } else {
            return redirect('/')->with(['error'=>true, 'email' => __($status)]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        if ($status === Password::ResetLinkSent) {
            return back()->with('status', __($status));
        } else {
            return back()->with(['error'=>true, 'email' => __($status)]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        auth()->user()->deletePushSubscription($request->endpoint);
        auth()->logout();
        return redirect('/');
    }
}
