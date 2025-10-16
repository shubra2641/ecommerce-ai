<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'enabled',
        'mode',
        'credentials',
        'transfer_details',
        'require_proof',
        'icon',
        'description',
        'sort_order'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'require_proof' => 'boolean',
        'credentials' => 'array',
        'sort_order' => 'integer'
    ];

    /**
     * Get the icon class for the gateway
     */
    public function getIconClassAttribute()
    {
        return $this->icon ?: 'fas fa-credit-card';
    }

    /**
     * Get the display name for the gateway
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: ucfirst($this->slug);
    }

    /**
     * Scope for enabled gateways
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for online gateways
     */
    public function scopeOnline($query)
    {
        return $query->whereIn('slug', ['paypal', 'stripe', 'tap']);
    }

    /**
     * Scope for offline gateways
     */
    public function scopeOffline($query)
    {
        return $query->whereIn('slug', ['cod', 'offline']);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'enabled',
        'mode',
        'credentials',
        'transfer_details',
        'require_proof',
        'icon',
        'description',
        'sort_order'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'require_proof' => 'boolean',
        'credentials' => 'array',
        'sort_order' => 'integer'
    ];

    /**
     * Get the icon class for the gateway
     */
    public function getIconClassAttribute()
    {
        return $this->icon ?: 'fas fa-credit-card';
    }

    /**
     * Get the display name for the gateway
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: ucfirst($this->slug);
    }

    /**
     * Scope for enabled gateways
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for online gateways
     */
    public function scopeOnline($query)
    {
        return $query->whereIn('slug', ['paypal', 'stripe', 'tap']);
    }

    /**
     * Scope for offline gateways
     */
    public function scopeOffline($query)
    {
        return $query->whereIn('slug', ['cod', 'offline']);
    }
}
