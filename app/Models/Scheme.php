<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    protected $table = "schemes";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['employee_id', 'scheme', 'base_amount', 'base_commision_rate'];
    protected $guarded = ['id'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
