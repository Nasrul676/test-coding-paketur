<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use App\Models\Manager;
use App\Models\Employee;

class Company extends Model
{
    use softDeletes, HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'email',
        'phone'
    ];

    /**
     * The roles that belong to the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get all of the managers for the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    /**
     * Get all of the employees for the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
