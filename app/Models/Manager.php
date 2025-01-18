<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;
use App\Models\User;

class Manager extends Model
{

    use SoftDeletes, HasFactory;

    protected $table = 'managers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_id',
        'user_id',
    ];

    /**
     * Get the company that owns the Manager
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * Get the user that owns the Manager
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
