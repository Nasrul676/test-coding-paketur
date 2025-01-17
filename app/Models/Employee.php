<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;

class Employee extends Model
{

    use SoftDeletes, HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_id',
    ];

    /**
     * Get the company that owns the Employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
