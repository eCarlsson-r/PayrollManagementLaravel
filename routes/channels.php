<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Account.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Workflow status page: only Admin/Manager may watch the live Band chat log.
Broadcast::channel('workflow', function ($user) {
    return in_array($user->type, ['Admin', 'Manager']);
});
