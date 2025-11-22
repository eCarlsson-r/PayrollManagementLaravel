<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PaymentCalculator;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\Document;
use App\Models\Timecard;
use App\Models\Receipt;

class PaymentCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_hourly_calculation_with_overtime()
    {
        $employee = Employee::create(['id' => 'SF910001', 'first_name' => 'Hourly', 'last_name' => 'OT', 'position' => 'Employee', 'dob' => '1990-01-01', 'email' => 'h_ot@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'HOURLY', 'base_amount' => 10]);

        $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Time Card', 'file_name' => 't.jpg', 'file_path' => '/storage/t.jpg', 'file' => '/storage/t.jpg', 'verified' => 'U']);

        // one timecard 09:00 - 19:00 = 10 hours
        Timecard::create(['document_id' => $doc->id, 'date' => date('Y-m-d', strtotime('monday this week')), 'time_start' => '09:00:00', 'time_end' => '19:00:00']);

        $calculator = new PaymentCalculator();
        $rows = $calculator->calculatePayments();

        $found = collect($rows)->firstWhere('employee_id', $employee->id);
        $this->assertNotNull($found, 'Hourly scheme row not found');
        // hours rounded 10 -> overtime multiplier 1.5 -> amount = 1.5 * (10 * 10) = 150
        $this->assertEquals(150, $found['amount']);
    }

    public function test_monthly_late_day_thresholds_at_month_end()
    {
        // choose a day that is month-end for test; use PaymentCalculator with today set to month-end
        $employee = Employee::create(['id' => 'SF910002', 'first_name' => 'Monthly', 'last_name' => 'Late', 'position' => 'Employee', 'dob' => '1990-01-02', 'email' => 'm_late@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'MONTHLY', 'base_amount' => 1000]);

        // create 4 documents each with a late timecard in the month -> lateDays = 4
        for ($i = 0; $i < 4; $i++) {
            $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Time Card', 'file_name' => "t{$i}.jpg", 'file_path' => '/storage/t.jpg', 'file' => '/storage/t.jpg', 'verified' => 'U']);
            Timecard::create(['document_id' => $doc->id, 'date' => date('Y-m-d', strtotime('first day of this month + ' . $i . ' days')), 'time_start' => '10:00:00', 'time_end' => '17:00:00']);
        }

        $calculator = new PaymentCalculator();
        $today = date('Y-m-t');
        $rows = $calculator->calculatePayments($today);

        $found = collect($rows)->firstWhere('employee_id', $employee->id);
        $this->assertNotNull($found, 'Monthly scheme row not found');
        // lateDays 4 -> amount = base_amount * 0.9
        $this->assertEquals(900, $found['amount']);
    }

    public function test_commission_non_month_end_returns_commission_only()
    {
        $employee = Employee::create(['id' => 'SF910003', 'first_name' => 'Comm', 'last_name' => 'Now', 'position' => 'Employee', 'dob' => '1990-01-03', 'email' => 'comm_now@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'COMMISSION', 'base_amount' => 0, 'base_commission_rate' => 10]);

        $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Sales Receipt', 'file_name' => 's.jpg', 'file_path' => '/storage/s.jpg', 'file' => '/storage/s.jpg', 'verified' => 'U']);
        Receipt::create(['document_id' => $doc->id, 'date' => date('Y-m-d'), 'amount' => 200]);

        $calculator = new PaymentCalculator();
        // use a non-month-end date
        $today = date('Y-m-d', strtotime('first day of this month'));
        $rows = $calculator->calculatePayments($today);

        $found = collect($rows)->firstWhere('employee_id', $employee->id);
        $this->assertNotNull($found, 'Commission scheme row not found');
        // commission = 200 * 10% = 20
        $this->assertEquals(20, $found['amount']);
    }

    public function test_commission_month_end_with_late_days_adjusts_amount()
    {
        $employee = Employee::create(['id' => 'SF910004', 'first_name' => 'Comm', 'last_name' => 'Late', 'position' => 'Employee', 'dob' => '1990-01-04', 'email' => 'comm_late@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'COMMISSION', 'base_amount' => 1000, 'base_commission_rate' => 10]);

        // commission doc
        $docComm = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Sales Receipt', 'file_name' => 's2.jpg', 'file_path' => '/storage/s2.jpg', 'file' => '/storage/s2.jpg', 'verified' => 'U']);
        Receipt::create(['document_id' => $docComm->id, 'date' => date('Y-m-d'), 'amount' => 100]);

        // 4 late timecards in month
        for ($i = 0; $i < 4; $i++) {
            $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Time Card', 'file_name' => "tm{$i}.jpg", 'file_path' => '/storage/tm.jpg', 'file' => '/storage/tm.jpg', 'verified' => 'U']);
            Timecard::create(['document_id' => $doc->id, 'date' => date('Y-m-d', strtotime('first day of this month + ' . $i . ' days')), 'time_start' => '10:00:00', 'time_end' => '17:00:00']);
        }

        $calculator = new PaymentCalculator();
        $today = date('Y-m-t');
        $rows = $calculator->calculatePayments($today);

        $found = collect($rows)->firstWhere('employee_id', $employee->id);
        $this->assertNotNull($found, 'Commission scheme row not found');
        // commission = 100 * 10% = 10; lateDays=4 -> amount = base*0.9 + commission = 900 + 10 = 910
        $this->assertEquals(910, $found['amount']);
    }
}
