<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Role;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the role that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user is a super admin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role = 'super_admin';
    }

    /**
     * Check if the user is a manager
     *
     * @return bool
     */
    public function isManager():bool
    {
        return $this->name === 'manager';
    }

    /**
     * Check if the user is an employee
     *
     * @return bool
     */
    public function isEmployee():bool
    {
        return $this->name === 'employee';
    }

    /**
     * Check if the user has permission
     *
     * @param \App\Models\Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        // lakukan caching untuk permission agar tidak membebani aplikasi terutama query ke database
        $userPermissions = Cache::remember(
            "user_{$this->id}_permissions",
            3600,
            fn() => $this->role->permissions
        );
        $permissionsName = $userPermissions->pluck('name')->toArray();

        return in_array($permission->name, $permissionsName, true);
    }

    public function clearPermissionCache(): void
    {
        Cache::forget("user_{$this->id}_permissions");
    }

}
