<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $table = "careers";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['employee_id', 'position', 'start_date', 'end_date', 'description'];
    protected $guarded = ['id'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
