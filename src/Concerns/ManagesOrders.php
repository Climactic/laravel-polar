<?php

namespace Climactic\LaravelPolar\Concerns;

use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components\OrderStatus;
use Climactic\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ManagesOrders
{
    /**
     * Get all of the orders for the billable.
     *
     * @return MorphMany<Order, covariant $this>
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(LaravelPolar::$orderModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Determine if the billable has purchased a specific product.
     */
    public function hasPurchasedProduct(string $productId): bool
    {
        return $this->orders()->where('product_id', $productId)->where('status', OrderStatus::Paid)->exists();
    }
}
