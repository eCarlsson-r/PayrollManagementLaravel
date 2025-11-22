<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\Employee;
use App\Models\Account;
use App\Models\Feedback;
use App\Notifications\FeedbackSent;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_feedback_and_notifies_manager()
    {
        Notification::fake();

        // create manager and account
        $manager = Employee::create([
            'id' => 'MG100000',
            'first_name' => 'Mgr',
            'last_name' => 'One',
            'position' => 'Manager',
            'dob' => '1980-01-01',
            'email' => 'mgr1@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'mgracct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);

        // create employee and account
        $employee = Employee::create([
            'id' => 'SF100000',
            'first_name' => 'Emp',
            'last_name' => 'One',
            'position' => 'Employee',
            'dob' => '1990-01-01',
            'email' => 'emp1@example.test',
            'contact' => '111',
            'address' => 'addr',
            'pay_method' => 'cash',
            'manager' => $manager->id
        ]);
        $employeeAccount = Account::create(['employee_id' => $employee->id, 'email' => 'empacct@example.test', 'password' => bcrypt('password'), 'type' => 'Employee']);

        $this->actingAs($employeeAccount);

        $response = $this->post('/feedback', [
            'title' => 'Test Title',
            'feedback' => 'This is a test feedback body.'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('feedback', [
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'title' => 'Test Title',
            'feedback' => 'This is a test feedback body.',
            'read' => 'U'
        ]);

        Notification::assertSentTo($managerAccount, FeedbackSent::class);
    }

    public function test_update_marks_feedback_as_read()
    {
        // create manager and account
        $manager = Employee::create([
            'id' => 'MG100001',
            'first_name' => 'Mgr2',
            'last_name' => 'Two',
            'position' => 'Manager',
            'dob' => '1980-01-02',
            'email' => 'mgr2@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'mgr2acct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);
        $employee = Employee::create(['id' => 'SF200000', 'first_name' => 'Em', 'last_name' => 'Ployee', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'employee_emp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        // create feedback
        $fb = Feedback::create([
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'time' => '09:00:00',
            'date' => '2025-11-20',
            'title' => 'Pending',
            'feedback' => 'Please read me',
            'read' => 'U'
        ]);

        $this->actingAs($managerAccount);

        // route for update is defined as GET /read/{id}
        $response = $this->get('/read/'.$fb->id);

        $response->assertStatus(302);

        $this->assertDatabaseHas('feedback', ['id' => $fb->id, 'read' => 'R']);
    }

    public function test_index_returns_feedback_list_for_manager()
    {
        $manager = Employee::create([
            'id' => 'MG100002',
            'first_name' => 'Mgr3',
            'last_name' => 'Three',
            'position' => 'Manager',
            'dob' => '1980-01-03',
            'email' => 'mgr3@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'mgr3acct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);

        // create the employee referenced by the feedback so the view can render employee data
        Employee::create([
            'id' => 'SF300000',
            'first_name' => 'Sub',
            'last_name' => 'Ord',
            'position' => 'Employee',
            'dob' => '1995-01-01',
            'email' => 'sub@example.test',
            'contact' => '222',
            'address' => 'addr',
            'pay_method' => 'cash',
            'manager' => $manager->id
        ]);

        Feedback::create([
            'employee_id' => 'SF300000',
            'manager' => $manager->id,
            'time' => '10:00:00',
            'date' => '2025-11-20',
            'title' => 'Hello',
            'feedback' => 'Body',
            'read' => 'U'
        ]);

        $this->actingAs($managerAccount);

        $response = $this->get('/feedback');

        $response->assertStatus(200);
        $response->assertSee('Body');
    }

    public function test_show_marks_notification_read()
    {

        $manager = Employee::create([
            'id' => 'MG200000',
            'first_name' => 'Noti',
            'last_name' => 'Mgr',
            'position' => 'Manager',
            'dob' => '1980-01-01',
            'email' => 'notimgr@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'notimgracct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);

        $employee = Employee::create([
            'id' => 'SF200001',
            'first_name' => 'Sender',
            'last_name' => 'User',
            'position' => 'Employee',
            'dob' => '1990-01-01',
            'email' => 'sender@example.test',
            'contact' => '111',
            'address' => 'addr',
            'pay_method' => 'cash',
            'manager' => $manager->id
        ]);

        $fb = Feedback::create([
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'time' => '09:00:00',
            'date' => '2025-11-20',
            'title' => 'Note',
            'feedback' => 'Please read',
            'read' => 'U'
        ]);

        // create a database notification row directly (avoid custom WebPush channel in tests)
        $notificationId = (string) \Illuminate\Support\Str::uuid();
        \DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => FeedbackSent::class,
            'notifiable_type' => get_class($managerAccount),
            'notifiable_id' => $managerAccount->id,
            'data' => json_encode([
                'id' => $fb->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'title' => $fb->title,
            ]),
            'created_at' => now(),
            'read_at' => null,
        ]);

        $notification = $managerAccount->unreadNotifications()->first();

        $this->actingAs($managerAccount);

        $response = $this->get('/feedback/'.$notification->id);

        $response->assertStatus(302);

        $mgrNotif = $managerAccount->notifications()->where('id', $notification->id)->first();
        $this->assertNotNull($mgrNotif->read_at);
    }
}
