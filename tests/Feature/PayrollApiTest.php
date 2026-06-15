<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\Timecard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class PayrollApiTest extends TestCase
{
    use RefreshDatabase;

    private string $key = 'test-band-api-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.band.api_key' => $this->key]);
    }

    /** Auth header helper. */
    private function authHeaders(): array
    {
        return ['X-API-Key' => $this->key];
    }

    public function test_calculate_requires_api_key()
    {
        $this->getJson('/api/payroll/calculate')->assertStatus(401);
    }

    public function test_calculate_rejects_wrong_key()
    {
        $this->getJson('/api/payroll/calculate', ['X-API-Key' => 'nope'])
            ->assertStatus(401);
    }

    public function test_calculate_returns_hourly_payroll()
    {
        $employee = Employee::create(['first_name' => 'Api', 'last_name' => 'Hourly', 'position' => 'Employee', 'dob' => '1990-01-01', 'email' => 'apihourly@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'HOURLY', 'base_amount' => 10]);

        $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Time Card', 'file_name' => 't.jpg', 'file_path' => '/storage/t.jpg', 'file' => '/storage/t.jpg', 'verified' => 'U']);
        $monday = date('Y-m-d', strtotime('monday this week'));
        Timecard::create(['document_id' => $doc->id, 'date' => $monday, 'time_start' => '09:00:00', 'time_end' => '17:00:00']);

        $response = $this->getJson('/api/payroll/calculate', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['employee_id' => $employee->id, 'amount' => 80]);
    }

    public function test_submit_creates_payment_and_requires_auth()
    {
        $employee = Employee::create(['first_name' => 'Api', 'last_name' => 'Submit', 'position' => 'Employee', 'dob' => '1990-01-02', 'email' => 'apisubmit@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'bank']);

        $payload = ['payments' => [['employee_id' => $employee->id, 'amount' => 5600000]]];

        // unauthenticated
        $this->postJson('/api/payroll/submit', $payload)->assertStatus(401);

        // authenticated
        $response = $this->postJson('/api/payroll/submit', $payload, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'created_count' => 1]);

        $this->assertDatabaseHas('payments', [
            'employee_id' => $employee->id,
            'amount' => 5600000,
            'method' => 'bank',
        ]);
    }

    public function test_submit_reports_unknown_employee()
    {
        $payload = ['payments' => [['employee_id' => 999999, 'amount' => 1000]]];

        $this->postJson('/api/payroll/submit', $payload, $this->authHeaders())
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'Employee not found.']);
    }

    public function test_flag_records_single_entry()
    {
        $response = $this->postJson('/api/payroll/flag', [
            'period' => '2026-06',
            'reason' => 'Net pay below UMR minimum',
            'severity' => 'critical',
            'gross_amount' => 4000000,
            'net_amount' => 3500000,
            'data' => ['umr' => 4900000],
        ], $this->authHeaders());

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'flagged_count' => 1]);

        $this->assertDatabaseHas('payroll_flags', [
            'severity' => 'critical',
            'resolved' => false,
        ]);
    }

    public function test_flag_records_batch()
    {
        $response = $this->postJson('/api/payroll/flag', [
            'flags' => [
                ['reason' => 'anomaly one'],
                ['reason' => 'anomaly two', 'severity' => 'critical'],
            ],
        ], $this->authHeaders());

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'flagged_count' => 2]);
    }

    public function test_flag_validates_missing_reason()
    {
        $response = $this->postJson('/api/payroll/flag', [
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'flagged_count' => 0]);
    }

    public function test_log_records_workflow_message()
    {
        $response = $this->postJson('/api/payroll/log', [
            'period' => '2026-06',
            'agent' => 'Data Collector',
            'type' => 'handoff',
            'content' => 'Collected 5 payroll rows.',
        ], $this->authHeaders());

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('workflow_messages', [
            'period' => '2026-06',
            'agent_name' => 'Data Collector',
            'message_type' => 'handoff',
            'content' => 'Collected 5 payroll rows.',
        ]);
    }

    public function test_log_requires_content()
    {
        $this->postJson('/api/payroll/log', ['period' => '2026-06'], $this->authHeaders())
            ->assertStatus(422);
    }

    public function test_flags_returns_decisions_for_period()
    {
        $this->postJson('/api/payroll/flag', [
            'flags' => [
                ['employee_id' => 8, 'period' => '2026-06', 'reason' => 'Below UMR'],
            ],
        ], $this->authHeaders())->assertStatus(201);

        $this->getJson('/api/payroll/flags?period=2026-06', $this->authHeaders())
            ->assertStatus(200)
            ->assertJson(['success' => true, 'count' => 1])
            ->assertJsonFragment(['employee_id' => '8', 'decision' => 'pending']);
    }
}
