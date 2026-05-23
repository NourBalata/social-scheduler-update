<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $user->pages()->create([
            'page_name' => $user->name,
            'page_id'   => null,
            'is_active' => false,
        ]);
    }
}