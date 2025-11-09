<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\Feedback;
use App\Models\Request;
use App\Models\History;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id', 'first_name', 'last_name', 'position', 'dob', 
        'email', 'contact', 'address', 'qualification',
        'career', 'pay_method', 'bank', 'bank_account', 'manager'
    ];
    protected $guarded = ['id'];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function feedbacks() {
        return $this->hasMany(Feedback::class);
    }

    public function requests() {
        return $this->hasMany(Request::class);
    }

    public function histories() {
        return $this->hasMany(History::class);
    }
}
