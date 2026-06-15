<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowMessage extends Model
{
    protected $table = 'workflow_messages';

    protected $fillable = [
        'period',
        'agent_name',
        'sender_type',
        'message_type',
        'content',
    ];
}
