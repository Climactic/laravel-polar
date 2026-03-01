<?php

namespace Climactic\LaravelPolar;

use Illuminate\Support\Carbon;
use Climactic\LaravelPolar\Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Polar\Models\Components\OrderInvoice;
use Polar\Models\Components\OrderStatus;
use Polar\Models\Components\Refund;
use Polar\Models\Components\RefundCreate;
use Polar\Models\Components\RefundReason;

/**
 * @property int $id
 * @property string $billable_type
 * @property int $billable_id
 * @property string|null $polar_id
 * @property OrderStatus $status
 * @property int $amount
 * @property int $tax_amount
 * @property int $refunded_amount
 * @property int $refunded_tax_amount
 * @property string $currency
 * @property string $billing_reason
 * @property string $customer_id
 * @property string $product_id
 * @property \Carbon\CarbonInterface|null $refunded_at
 * @property \Carbon\CarbonInterface $ordered_at
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 * @property \Climactic\LaravelPolar\Billable $billable
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'polar_orders';

    /**
    * The attributes that are not mass assignable.
    *
    * @var array<string>
    */
    protected $guarded = [];

    /**
     * Get the billable model related to the customer.
     *
     * @return MorphTo<Model, covariant $this>
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the order is paid.
     */
    public function paid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    /**
     * Filter query by paid.
     *
     * @param  Builder<Order>  $query
     */
    public function scopePaid(Builder $query): void
    {
        $query->where('status', OrderStatus::Paid);
    }

    /**
     * Check if the order is refunded.
     */
    public function refunded(): bool
    {
        return $this->status === OrderStatus::Refunded;
    }

    /**
     * Filter query by refunded.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeRefunded(Builder $query): void
    {
        $query->where('status', OrderStatus::Refunded);
    }

    /**
     * Check if the order is partially refunded.
     */
    public function partiallyRefunded(): bool
    {
        return $this->status === OrderStatus::PartiallyRefunded;
    }

    /**
     * Filter query by partially refunded.
     *
     * @param  Builder<Order>  $query
     */
    public function scopePartiallyRefunded(Builder $query): void
    {
        $query->where('status', OrderStatus::PartiallyRefunded);
    }

    /**
     * Check if the order is void.
     */
    public function void(): bool
    {
        return $this->status === OrderStatus::Void;
    }

    /**
     * Filter query by void.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeVoid(Builder $query): void
    {
        $query->where('status', OrderStatus::Void);
    }

    /**
     * Determine if the order is for a specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return $this->product_id === $productId;
    }

    /**
     * Get the invoice for this order.
     *
     * @throws \RuntimeException if the order has no polar_id
     */
    public function invoice(): OrderInvoice
    {
        if ($this->polar_id === null) {
            throw new \RuntimeException('Cannot retrieve invoice for an order without a polar_id.');
        }

        return LaravelPolar::getOrderInvoice($this->polar_id);
    }

    /**
     * Generate/trigger invoice creation for this order.
     *
     * @throws \RuntimeException if the order has no polar_id
     */
    public function generateInvoice(): void
    {
        if ($this->polar_id === null) {
            throw new \RuntimeException('Cannot generate invoice for an order without a polar_id.');
        }

        LaravelPolar::generateOrderInvoice($this->polar_id);
    }

    /**
     * Refund this order (full or partial).
     *
     * @throws \RuntimeException if the order has no polar_id
     */
    public function issueRefund(int $amount, ?RefundReason $reason = null): Refund
    {
        if ($this->polar_id === null) {
            throw new \RuntimeException('Cannot refund an order without a polar_id.');
        }

        $request = new RefundCreate(
            orderId: $this->polar_id,
            reason: $reason ?? RefundReason::Other,
            amount: $amount,
        );

        return LaravelPolar::createRefund($request);
    }

    /**
     * Sync the order with the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'polar_id' => $attributes['id'],
            'status' => \is_string($attributes['status']) ? OrderStatus::from($attributes['status']) : $attributes['status'],
            'amount' => $attributes['amount'],
            'tax_amount' => $attributes['tax_amount'],
            'refunded_amount' => $attributes['refunded_amount'],
            'refunded_tax_amount' => $attributes['refunded_tax_amount'],
            'currency' => $attributes['currency'],
            'billing_reason' => $attributes['billing_reason'],
            'customer_id' => $attributes['customer_id'],
            'product_id' => $attributes['product_id'],
            'refunded_at' => isset($attributes['refunded_at']) ? Carbon::make($attributes['refunded_at']) : null,
            'ordered_at' => Carbon::make($attributes['created_at']),
        ]);

        return $this;
    }

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'ordered_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
