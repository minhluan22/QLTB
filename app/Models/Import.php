<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Import (Nhập thiết bị)
 *
 * Ghi lại mỗi lần nhập thiết bị vào kho
 */
class Import extends Model
{
    protected $fillable = [
        'device_id',
        'quantity',
        'price',
        'supplier',
        'country',
        'brand',
        'production_year',
        'import_date',
        'note',
        'imported_by',
    ];

    protected $casts = [
        'import_date' => 'date',
        'price'       => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Lô nhập thuộc về một thiết bị
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Lô nhập do một admin thực hiện
     */
    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
