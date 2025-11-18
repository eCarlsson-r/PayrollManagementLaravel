<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\Feedback;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Scheme;
use App\Models\Career;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id', 'first_name', 'last_name', 'position', 'dob', 
        'email', 'contact', 'address', 'manager', 
        'pay_method', 'bank', 'bank_account'
    ];
    protected $guarded = ['id'];

    protected $attributes = [
        'bank' => '',
        'bank_account' => '',
        'manager' => ''
    ];

    public function account() {
        return $this->hasOne(Account::class);
    }

    public function scheme() {
        return $this->hasOne(Scheme::class);
    }

    public function feedbacks() {
        return $this->hasMany(Feedback::class);
    }

    public function requests() {
        return $this->hasMany(Document::class);
    }

    public function histories() {
        return $this->hasMany(Payment::class);
    }

    public function career() {
        return $this->hasMany(Career::class, 'employee_id');
    }
}
