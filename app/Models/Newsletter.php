<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

/**
 * Newsletter Model
 * 
 * Handles newsletter subscription management including
 * subscription, unsubscription, and email management.
 */
class Newsletter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletter_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'name',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->unsubscribe_token)) {
                $model->unsubscribe_token = Str::random(32);
            }
        });
    }

    /**
     * Scope for active subscribers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive subscribers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for unsubscribed users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    /**
     * Subscribe a new email to newsletter
     *
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public static function subscribe($email, $name = null)
    {
        return self::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'status' => 'active',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'unsubscribe_token' => Str::random(32)
            ]
        );
    }

    /**
     * Unsubscribe an email from newsletter
     *
     * @param string $email
     * @return bool
     */
    public static function unsubscribe($email)
    {
        $subscriber = self::where('email', $email)->first();
        
        if ($subscriber) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Unsubscribe using token
     *
     * @param string $token
     * @return bool
     */
    public static function unsubscribeByToken($token)
    {
        $subscriber = self::where('unsubscribe_token', $token)->first();
        
        if ($subscriber) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Get active subscribers count
     *
     * @return int
     */
    public static function getActiveCount()
    {
        return self::active()->count();
    }

    /**
     * Get all active subscribers for email sending
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveSubscribers()
    {
        return self::active()->get();
    }

    /**
     * Check if email is subscribed
     *
     * @param string $email
     * @return bool
     */
    public static function isSubscribed($email)
    {
        return self::where('email', $email)
                   ->where('status', 'active')
                   ->exists();
    }

    /**
     * Get unsubscribe URL
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return route('newsletter.unsubscribe', ['token' => $this->unsubscribe_token]);
    }
}
