<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,  HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public const STAFF_ROLES = ['staff', 'admin', 'owner', 'super_admin'];

    public function isStaffMember(): bool
    {
        return $this->hasAnyRole(self::STAFF_ROLES);
    }


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);

    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);

    }

    public function assignableRoles(): array
    {
        return $this->hasRole('super_admin')
            ? ['staff', 'admin', 'owner']
            : ['staff', 'admin'];
    }

    public function canManageStaff(User $target): bool
    {
        return $this->isNot($target) && $target->hasAnyRole($this->assignableRoles());
    }

}
