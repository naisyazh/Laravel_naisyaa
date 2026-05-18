<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestCustomerService
{
    public function resolveForCheckout(?User $authenticatedUser = null): User
    {
        if ($authenticatedUser && ! $authenticatedUser->isVendor()) {
            return $authenticatedUser;
        }

        $temporaryKey = Str::lower((string) Str::ulid());

        $guest = User::create([
            'name' => 'Guest',
            'email' => "guest-{$temporaryKey}@toko.local",
            'password' => Hash::make(Str::random(40)),
            'role' => 'user',
            'is_guest' => true,
        ]);

        $guestName = 'Guest_' . str_pad((string) $guest->id, 7, '0', STR_PAD_LEFT);

        $guest->forceFill([
            'name' => $guestName,
            'email' => Str::lower($guestName) . '@toko.local',
        ])->save();

        return $guest;
    }
}
