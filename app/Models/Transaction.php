<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'users_id',
        'address',
        'total_price',
        'shipping_price',
        'payment',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function details() {
        return $this->hasMany(TransactionDetail::class, 'transactions_id', 'id');
    }
}
