<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\Employee;

class Account extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
     use HasFactory, Notifiable, \Illuminate\Auth\Passwords\CanResetPassword;
     
    protected $table = 'accounts';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['employee_id', 'email', 'password', 'type'];
    protected $guarded = ['id'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
