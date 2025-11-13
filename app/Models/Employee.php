<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\Feedback;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Scheme;
use App\Models\Career;

class Employee extends Model
{
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

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function scheme() {
        return $this->belongsTo(Scheme::class);
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
