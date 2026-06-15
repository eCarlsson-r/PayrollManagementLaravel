<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Employee;
use App\Models\PayrollFlag;
use App\Models\WorkflowMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class WorkflowControllerTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $type): Account
    {
        $emp = Employee::create([
            'first_name' => $type, 'last_name' => 'User', 'position' => $type,
            'dob' => '1985-01-01', 'email' => strtolower($type).'@wf.test', 'contact' => '000',
            'address' => 'addr', 'pay_method' => 'cash',
        ]);

        return Account::create([
            'employee_id' => $emp->id, 'email' => strtolower($type).'acct@wf.test',
            'password' => bcrypt('password'), 'type' => $type,
        ]);
    }

    public function test_workflow_page_renders_for_admin()
    {
        WorkflowMessage::create([
            'period' => '2026-06', 'agent_name' => 'Data Collector',
            'sender_type' => 'Agent', 'message_type' => 'handoff', 'content' => 'Collected 5 rows.',
        ]);
        PayrollFlag::create([
            'employee_id' => '8', 'period' => '2026-06', 'reason' => 'Below UMR',
            'severity' => 'critical', 'decision' => 'pending', 'resolved' => false,
        ]);

        $this->actingAs($this->account('Admin'));

        $this->get('/workflow')
            ->assertStatus(200)
            ->assertSee('Payroll Workflow')
            ->assertSee('Collected 5 rows.')
            ->assertSee('Employee 8');
    }

    public function test_manager_can_access_workflow()
    {
        $this->actingAs($this->account('Manager'));
        $this->get('/workflow')->assertStatus(200);
    }

    public function test_employee_cannot_access_workflow()
    {
        // CheckAccountType middleware redirects unauthorized roles away.
        $this->actingAs($this->account('Employee'));
        $this->get('/workflow')->assertStatus(302);
    }

    public function test_resolve_flag_sets_decision()
    {
        $flag = PayrollFlag::create([
            'employee_id' => '8', 'period' => '2026-06', 'reason' => 'Below UMR',
            'severity' => 'critical', 'decision' => 'pending', 'resolved' => false,
        ]);

        $this->actingAs($this->account('Admin'));

        $this->postJson("/workflow/flag/{$flag->id}", ['decision' => 'approved'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'decision' => 'approved']);

        $this->assertDatabaseHas('payroll_flags', [
            'id' => $flag->id, 'decision' => 'approved', 'resolved' => true,
        ]);
    }

    public function test_resolve_flag_rejects_bad_decision()
    {
        $flag = PayrollFlag::create([
            'employee_id' => '8', 'period' => '2026-06', 'reason' => 'x',
            'decision' => 'pending', 'resolved' => false,
        ]);
        $this->actingAs($this->account('Admin'));

        $this->postJson("/workflow/flag/{$flag->id}", ['decision' => 'maybe'])
            ->assertStatus(422);
    }
}
