<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'stock',
        'stock_in',
        'stock_out',
        'price',
        'weight',
        'image',
        'minimum_stock',
    ];

    /**
     * Add prefix to image
     */
    public function getImageAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    /**
     * The delivery that belong to the inventory.
     */
    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class, 'delivery_inventory', 'inventory_id', 'delivery_id')->withPivot('quantity');
    }

    /**
     * Add or Update Image
     */
    public function addOrUpdateImage($image, $id): void
    {
        \Illuminate\Support\Facades\Log::info('addOrUpdateImage', ['image' => $image, 'id' => $id]);
        if ($id > 0) {
            Storage::disk('public')->delete($this->image);
        }
        $this->forceFill(['image' => $image->storePublicly('inventory-images', ['disk' => 'public'])])->save();
    }

    /**
     * Remove Image
     */
    public function removeImage(): void
    {
        Storage::disk('public')->delete($this->image);
        $this->forceFill(['image' => null])->save();
    }
}
