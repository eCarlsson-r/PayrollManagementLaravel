<?php

namespace App\Services;

use App\Models\Scheme;
use App\Models\Document;
use App\Models\Timecard;
use App\Models\Receipt;

class PaymentCalculator
{
    /**
     * Calculate payments for different schemes.
     * Returns an array of payment rows suitable for the PaymentCreate view.
     */
    public function calculatePayments(?string $today = null): array
    {
        $calculation = [];

        $today = $today ?? date('Y-m-d');

        // HOURLY
        $hourlyScheme = Scheme::where('scheme', 'HOURLY')->get();
        foreach ($hourlyScheme as $scheme) {
            $hourlyDocs = Document::where('employee_id', $scheme->employee_id)->where('subject', 'Time Card')->get();
            $hours_worked = 0;
            $base_amount = $scheme->base_amount;

            foreach ($hourlyDocs as $doc) {
                $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                    'date',
                    [
                        date('Y-m-d', strtotime('monday this week')),
                        date('Y-m-d', strtotime('friday this week')),
                    ]
                )->first();

                if ($timecard == null) continue;

                $startTimestamp = strtotime($timecard->time_start);
                $endTimestamp = strtotime($timecard->time_end);
                $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                $differenceInHours = $differenceInSeconds / 3600;
                $hours_worked += round($differenceInHours);
            }

            $amount = ($hours_worked > 8) ? 1.5 * ($hours_worked * $base_amount) : $hours_worked * $base_amount;

            $calculation[] = [
                'employee_id' => $scheme->employee_id,
                'hours_worked' => $hours_worked,
                'base_amount' => $base_amount,
                'amount' => $amount,
            ];
        }

        // MONTHLY (only at month end)
        if ($today == date('Y-m-t')) {
            $salaryScheme = Scheme::where('scheme', 'MONTHLY')->get();
            foreach ($salaryScheme as $scheme) {
                $monthlyDocs = Document::where('employee_id', $scheme->employee_id)->where('subject', 'Time Card')->get();
                $base_amount = $scheme->base_amount;
                $lateDays = 0;

                foreach ($monthlyDocs as $doc) {
                    $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                        'date',
                        [
                            date('Y-m-01'),
                            date('Y-m-t'),
                        ]
                    )->first();

                    if ($timecard == null) continue;
                    $startTimestamp = strtotime($timecard->time_start);
                    $endTimestamp = strtotime('09:00 AM');
                    $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                    if ($differenceInSeconds > 0) $lateDays++;
                }

                if ($lateDays > 5) {
                    $amount = 0;
                } elseif ($lateDays >= 4) {
                    $amount = $base_amount * 0.9;
                } else {
                    $amount = $base_amount;
                }

                $calculation[] = [
                    'employee_id' => $scheme->employee_id,
                    'late_days' => $lateDays,
                    'base_amount' => $base_amount,
                    'amount' => $amount,
                ];
            }
        }

        // COMMISSION
        $commissionScheme = Scheme::where('scheme', 'COMMISSION')->get();
        foreach ($commissionScheme as $scheme) {
            $monthlyDocs = Document::where('employee_id', $scheme->employee_id)->where('subject', 'Time Card')->get();
            $commissionDocs = Document::where('employee_id', $scheme->employee_id)->where('subject', 'Sales Receipt')->get();
            $base_amount = $scheme->base_amount;
            $base_commission_rate = $scheme->base_commission_rate ?? 0; // percent
            $lateDays = 0;
            $commission = 0;

            foreach ($commissionDocs as $comm) {
                $commDoc = Receipt::where('document_id', $comm->id)->first();
                if ($commDoc == null) continue;
                $commission += $commDoc->amount * ($base_commission_rate / 100);
            }

            if ($today == date('Y-m-t')) {
                foreach ($monthlyDocs as $doc) {
                    $timecard = Timecard::where('document_id', $doc->id)->whereBetween(
                        'date',
                        [
                            date('Y-m-01'),
                            date('Y-m-t'),
                        ]
                    )->first();

                    if ($timecard == null) continue;
                    $startTimestamp = strtotime($timecard->time_start);
                    $endTimestamp = strtotime('09:00 AM');
                    $differenceInSeconds = abs($endTimestamp - $startTimestamp);
                    if ($differenceInSeconds > 0) $lateDays++;
                }

                if ($lateDays > 5) {
                    $amount = $commission;
                } elseif ($lateDays >= 4) {
                    $amount = ($base_amount * 0.9) + $commission;
                } else {
                    $amount = $base_amount + $commission;
                }
            } else {
                $amount = $commission;
            }

            $calculation[] = [
                'employee_id' => $scheme->employee_id,
                'commision' => $commission,
                'base_amount' => $base_amount,
                'late_days' => $lateDays,
                'amount' => $amount,
            ];
        }

        return $calculation;
    }
}
