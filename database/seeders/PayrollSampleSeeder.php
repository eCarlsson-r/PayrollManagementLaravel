<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\Document;
use App\Models\Timecard;
use App\Models\Receipt;

/**
 * Sample payroll data for the Band agent pipeline (Day 2).
 *
 * Populates the Scheme / Document / Timecard / Receipt rows that
 * PaymentCalculator (and therefore GET /api/payroll/calculate) reads.
 * The base DatabaseSeeder only creates login accounts, which is not
 * enough for the agents to compute anything.
 *
 * Notes on PaymentCalculator behaviour this data is shaped around:
 *  - HOURLY is computed every day from the current Mon-Fri window, so
 *    these employees always return a non-zero amount.
 *  - MONTHLY / COMMISSION base pay is only computed when the request
 *    date is month-end (?date=Y-m-t); off month-end, COMMISSION returns
 *    just the commission portion and MONTHLY returns nothing.
 *  - "Late days" are counted whenever time_start differs from 09:00 (the
 *    calculator uses abs()), so every shift here starts exactly at 09:00
 *    to keep results predictable (zero late days).
 *
 * Idempotent: keyed on email, so re-running `db:seed` will not duplicate.
 */
class PayrollSampleSeeder extends Seeder
{
    public function run(): void
    {
        // HOURLY — full work week (Mon-Fri, 8h/day) in the current week.
        $this->hourly('Budi', 'Santoso', 'budi.santoso@payroll.test', 75000);
        $this->hourly('Siti', 'Rahayu', 'siti.rahayu@payroll.test', 60000);

        // MONTHLY — salaried, computed at month-end.
        // Andi: well above Jakarta UMR (~Rp 4.9M) -> clean payslip.
        $this->monthly('Andi', 'Wijaya', 'andi.wijaya@payroll.test', 8000000);
        // Dewi: below UMR -> Agent 3 (compliance) should flag this one.
        $this->monthly('Dewi', 'Lestari', 'dewi.lestari@payroll.test', 4000000);

        // COMMISSION — base + 5% of sales receipts.
        $this->commission('Rina', 'Permata', 'rina.permata@payroll.test', 5000000, 5.0);
    }

    private function makeEmployee(string $first, string $last, string $email): ?Employee
    {
        if (Employee::where('email', $email)->exists()) {
            return null; // already seeded
        }

        return Employee::create([
            'first_name' => $first,
            'last_name' => $last,
            'position' => 'Employee',
            'dob' => '1992-01-01',
            'email' => $email,
            'contact' => '081200000000',
            'address' => 'Jakarta, Indonesia',
            'pay_method' => 'bank',
            'bank' => 'BCA',
            'bank_account' => '1234567890',
        ]);
    }

    /** Create a "Time Card" document holding a single day's timecard. */
    private function timecard(Employee $e, string $date, string $start, string $end): void
    {
        $doc = Document::create([
            'employee_id' => $e->id,
            'manager' => '',
            'subject' => 'Time Card',
            'file' => '',
            'file_name' => 'timecard.jpg',
            'file_path' => '/storage/timecard.jpg',
            'verified' => 'V',
        ]);

        Timecard::create([
            'document_id' => $doc->id,
            'date' => $date,
            'time_start' => $start,
            'time_end' => $end,
        ]);
    }

    private function hourly(string $first, string $last, string $email, float $rate): void
    {
        $e = $this->makeEmployee($first, $last, $email);
        if (!$e) return;

        Scheme::create([
            'employee_id' => $e->id,
            'scheme' => 'HOURLY',
            'base_amount' => $rate,
            'base_commission_rate' => 0,
        ]);

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            $this->timecard($e, date('Y-m-d', strtotime("$day this week")), '09:00:00', '17:00:00');
        }
    }

    private function monthly(string $first, string $last, string $email, float $salary): void
    {
        $e = $this->makeEmployee($first, $last, $email);
        if (!$e) return;

        Scheme::create([
            'employee_id' => $e->id,
            'scheme' => 'MONTHLY',
            'base_amount' => $salary,
            'base_commission_rate' => 0,
        ]);

        // Attendance for the first 10 days of the current month, all on time.
        for ($d = 1; $d <= 10; $d++) {
            $this->timecard($e, date('Y-m-') . sprintf('%02d', $d), '09:00:00', '17:00:00');
        }
    }

    private function commission(string $first, string $last, string $email, float $base, float $rate): void
    {
        $e = $this->makeEmployee($first, $last, $email);
        if (!$e) return;

        Scheme::create([
            'employee_id' => $e->id,
            'scheme' => 'COMMISSION',
            'base_amount' => $base,
            'base_commission_rate' => $rate,
        ]);

        // Attendance (for the month-end base portion), all on time.
        for ($d = 1; $d <= 8; $d++) {
            $this->timecard($e, date('Y-m-') . sprintf('%02d', $d), '09:00:00', '17:00:00');
        }

        // Sales receipts. receipts.amount is decimal(8,2) (max 999,999.99),
        // so several smaller receipts stand in for monthly sales.
        $amounts = [950000.00, 875000.00, 600000.00];
        foreach ($amounts as $i => $amount) {
            $doc = Document::create([
                'employee_id' => $e->id,
                'manager' => '',
                'subject' => 'Sales Receipt',
                'file' => '',
                'file_name' => 'receipt.jpg',
                'file_path' => '/storage/receipt.jpg',
                'verified' => 'V',
            ]);

            Receipt::create([
                'document_id' => $doc->id,
                'date' => date('Y-m-') . sprintf('%02d', $i + 1),
                'amount' => $amount,
            ]);
        }
    }
}
