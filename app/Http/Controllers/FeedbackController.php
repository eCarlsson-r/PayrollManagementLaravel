<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Employee;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('FeedbackList', ['feedbacks'=> Feedback::where('manager', '=', auth()->user()->employee->id)->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('FeedbackForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $feedbackData = [
            'employee_id' => auth()->user()->employee->id,
            'manager' => auth()->user()->employee->manager,
            'time' => date('H:m:s'),
            'date' => date('Y-m-d'),
            'read' => 'U'
        ];

        Feedback::create(
            array_merge(
                $feedbackData, request()->only(['title', 'feedback'])
            )
        );

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        Feedback::find($id)->update(['read' => 'R']);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        return Feedback::destroy($feedback);
    }
}
