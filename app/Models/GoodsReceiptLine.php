<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    protected $fillable = ['goods_receipt_id', 'ingredient_id', 'ordered_qty', 'previously_received_qty', 'received_now_qty', 'rejected_qty'];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function acceptedQty(): float
    {
        return max(0, $this->received_now_qty - $this->rejected_qty);
    }
}
