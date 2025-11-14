<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Scheme;
use App\Models\Timecard;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->type == "Admin") {
            return view('PaymentList', ['admin_payment'=> Payment::where('employee_id', '!=', auth()->user()->employee->id)->get()]);
        } else {
            return view('PaymentList', ['personal_payment'=> Payment::where('employee_id', auth()->user()->employee->id)->get()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $calculation = array();

        $hourlyScheme = Scheme::where('scheme', 'HOURLY')->get();
        foreach($hourlyScheme as $employee) {
            $hourly = Document::where('employee_id', $employee->employee_id)->where('subject', 'Time Card')->get();
            $hours_worked = 0;
            $base_amount = $employee->base_amount;
            foreach($hourly as $doc) {
                $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                    'date', 
                    [
                        date('Y-m-d', strtotime('monday this week')), 
                        date('Y-m-d', strtotime('friday this week'))
                    ]
                )->first();

                if ($timecard == null) continue;
                $startTimestamp = strtotime($timecard->time_start);
                $endTimestamp = strtotime($timecard->time_end);
                // Calculate the difference in seconds
                $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                // Convert the difference from seconds to hours
                $differenceInHours = $differenceInSeconds / 3600;
                $hours_worked += round($differenceInHours);
            }

            array_push($calculation, array(
                'employee_id' => $employee->employee_id,
                'hours_worked' => $hours_worked,
                'base_amount' => $base_amount,
                'amount' => ($hours_worked > 8)?1.5*($hours_worked * $base_amount):$hours_worked * $base_amount
            ));
        }

        if (date('Y-m-d') == date('Y-m-t')) {
            $salaryScheme = Scheme::where('scheme', 'MONTHLY')->get();
            foreach($salaryScheme as $employee) {
                $monthly = Document::where('employee_id', $employee->employee_id)->where('subject', 'Time Card')->get();
                $base_amount = $employee->base_amount;
                $lateDays = 0;

                foreach($monthly as $doc) {
                    $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                        'date', 
                        [
                            date('Y-m-01'), 
                            date('Y-m-t')
                        ]
                    )->first();

                    if ($timecard == null) continue;
                    $startTimestamp = strtotime($timecard->time_start);
                    $endTimestamp = strtotime("09:00 AM");
                    // Calculate the difference in seconds
                    $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                    if ($differenceInSeconds > 0) $lateDays++;
                }

                if ($lateDays > 5) {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'amount' => 0
                    ));
                } else if ($lateDays >= 4) {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'amount' => $base_amount * 0.9
                    ));
                } else {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'amount' => $base_amount
                    ));
                }
            }
        }
        
        $commissionScheme = Scheme::where('scheme', 'COMMISSION')->get();
        foreach($commissionScheme as $employee) {
            $monthly = Document::where('employee_id', $employee->employee_id)->where('subject', 'Time Card')->get();
            $commission = Document::where('employee_id', $employee->employee_id)->where('subject', 'Sales Receipt')->get();
            $base_amount = $employee->base_amount;
            $base_commission = $employee->base_commission_rate;
            $lateDays = 0;
            $commision = 0;

            foreach($commission as $comm) {
                $commDoc = Receipt::where('document_id', $comm->id)->first();
                if ($commDoc == null) continue;
                $commision += $commDoc->amount * ($employee->base_commission / 100);
            }

            if (date('Y-m-d') == date('Y-m-t')) {
                foreach($monthly as $doc) {
                    $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                        'date', 
                        [
                            date('Y-m-01'), 
                            date('Y-m-t')
                        ]
                    )->first();

                    if ($timecard == null) continue;
                    $startTimestamp = strtotime($timecard->time_start);
                    $endTimestamp = strtotime("09:00 AM");
                    // Calculate the difference in seconds
                    $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                    if ($differenceInSeconds > 0) $lateDays++;
                }

                if ($lateDays > 5) {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'commision' => $commision,
                        'amount' => $commision
                    ));
                } else if ($lateDays >= 4) {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'commision' => $commision,
                        'amount' => ($base_amount * 0.9) + $commision
                    ));
                } else {
                    array_push($calculation, array(
                        'employee_id' => $employee->employee_id,
                        'late_days' => $lateDays,
                        'base_amount' => $base_amount,
                        'commision' => $commision,
                        'amount' => $base_amount + $commision
                    ));
                }
            } else {
                array_push($calculation, array(
                    'employee_id' => $employee->employee_id,
                    'commision' => $commision,
                    'amount' => $commision
                ));
            }
        }

        return view('PaymentCreate', ['payments_to_made' => $calculation]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $Payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $Payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $Payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $Payment)
    {
        //
    }
}
