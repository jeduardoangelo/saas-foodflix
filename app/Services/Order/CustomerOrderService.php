<?php

namespace App\Services\Order;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerOrderService
{
    public function forUser(User $user): LengthAwarePaginator
    {
        return $user->orders()
            ->latest('created_at')
            ->latest('id')
            ->paginate(10);
    }
}
