<?php

namespace App\Services\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function forUser(User $user): Collection
    {
        return $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    public function create(User $user, array $attributes): Address
    {
        return DB::transaction(function () use ($user, $attributes) {
            $attributes['is_default'] = (bool) ($attributes['is_default'] ?? ! $user->addresses()->exists());

            if ($attributes['is_default']) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create($attributes);
        });
    }

    public function update(Address $address, array $attributes): Address
    {
        return DB::transaction(function () use ($address, $attributes) {
            if (! empty($attributes['is_default'])) {
                $address->user->addresses()
                    ->whereKeyNot($address->getKey())
                    ->update(['is_default' => false]);
            }

            $address->update($attributes);

            return $address->refresh();
        });
    }
}
