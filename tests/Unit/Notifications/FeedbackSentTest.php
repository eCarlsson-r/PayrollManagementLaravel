<?php

namespace Tests\Unit\Notifications;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Feedback;
use App\Notifications\FeedbackSent;

class FeedbackSentTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_database_payload()
    {
        $employee = Employee::create([
            'id' => 'SF900001',
            'first_name' => 'Unit',
            'last_name' => 'Tester',
            'position' => 'Employee',
            'dob' => '1990-01-01',
            'email' => 'unit@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);

        $feedback = Feedback::create([
            'employee_id' => $employee->id,
            'manager' => 'MG900001',
            'time' => '09:00:00',
            'date' => '2025-11-20',
            'title' => 'Unit Test',
            'feedback' => 'Payload',
            'read' => 'U'
        ]);

        $notif = new FeedbackSent($feedback);
        $payload = $notif->toBroadcast($employee);

        $this->assertIsArray($payload);
        $this->assertEquals($feedback->id, $payload['id']);
        $this->assertStringContainsString($employee->first_name, $payload['employee_name']);
        $this->assertEquals('Unit Test', $payload['title']);
    }
}
