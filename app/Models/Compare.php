<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Compare extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'product_id', 'price', 'compare_at_price'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for current user/session
     */
    public function scopeMine($query)
    {
        if (Auth::check()) {
            return $query->where('user_id', Auth::id());
        }
        return $query->where('session_id', session()->getId());
    }

    /**
     * Get compare items for user or session
     * 
     * @param int|null $userId
     * @param string|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCompareItems($userId = null, $sessionId = null)
    {
        $query = self::with('product');
        
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }
        
        return $query->get();
    }
}
