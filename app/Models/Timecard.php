<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timecard extends Model
{
    protected $table = "timecards";
    protected $primaryKey = 'id';
    protected $fillable = ['document_id', 'date', 'time_start', 'time_end'];
    protected $guarded = ['id'];
    public $timestamps = false;

    public function document() {
        return $this->belongsTo(Document::class);
    }
}
