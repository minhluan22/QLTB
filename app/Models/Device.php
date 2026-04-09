<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Device (Thiết bị)
 *
 * Công thức số lượng:
 *   remaining_qty  = quantity - damaged_qty - lost_qty         (còn lại dùng được)
 *   borrowed_qty   = quantity - available_qty - damaged_qty - lost_qty  (đang mượn)
 *   available_qty  = số lượng thực tế có thể cho mượn ngay bây giờ
 */
class Device extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'subject',          // Môn học
        'subject_group',    // Nhóm môn học
        'unit',             // Đơn vị (Cái, Bộ...)
        'specification',    // Quy cách
        'country',          // Nước SX
        'unit_price',       // Đơn giá
        'quantity',         // Tổng số lượng nhập
        'available_qty',    // Số lượng hiện có thể cho mượn
        'damaged_qty',      // Số lượng hỏng (tích lũy)
        'lost_qty',         // Số lượng mất (tích lũy)
        'status',
        'is_proposed',      // Đánh dấu thiết bị do giáo viên đề xuất
        'description',
    ];

    protected $casts = [
        'unit_price'    => 'decimal:2',
        'quantity'      => 'integer',
        'available_qty' => 'integer',
        'damaged_qty'   => 'integer',
        'lost_qty'      => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    public function borrowDetails()
    {
        return $this->hasMany(BorrowDetail::class);
    }

    public function damages()
    {
        return $this->hasMany(Damage::class);
    }

    // ==================== COMPUTED PROPERTIES ====================

    /**
     * Còn lại (dùng được) = Tổng - Hỏng - Mất
     * Đây là số lượng chưa bị loại khỏi sử dụng
     */
    public function remainingQty(): int
    {
        return max(0, $this->quantity - $this->damaged_qty - $this->lost_qty);
    }

    /**
     * Số đang cho mượn = Còn lại - Có thể mượn ngay
     */
    public function borrowedQty(): int
    {
        return max(0, $this->remainingQty() - $this->available_qty);
    }

    /**
     * Tổng giá trị = Tổng (Số lượng nhập × Đóng giá nhập) của các đợt nhập
     * Nếu không có lịch sử nhập thì tính bằng Số lượng × Đơn giá thủ công
     */
    public function totalValue(): float
    {
        if ($this->imports()->exists()) {
            return (float) $this->imports()->sum(\Illuminate\Support\Facades\DB::raw('quantity * price'));
        }
        return ($this->quantity ?? 0) * ($this->unit_price ?? 0);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Badge trạng thái Bootstrap
     */
    public function statusBadge(): string
    {
        return match($this->status) {
            'available'   => '<span class="badge bg-success">Sẵn sàng</span>',
            'borrowed'    => '<span class="badge bg-warning text-dark">Đang mượn</span>',
            'maintenance' => '<span class="badge bg-info">Bảo trì</span>',
            'damaged'     => '<span class="badge bg-danger">Hỏng</span>',
            default       => '<span class="badge bg-secondary">Không rõ</span>',
        };
    }

    /**
     * Cập nhật trạng thái tự động dựa vào available_qty và damaged_qty
     *
     * Logic:
     *  - Nếu tất cả hỏng/mất → damaged
     *  - Nếu đang có người mượn hết → borrowed
     *  - Ngược lại → available
     */
    public function updateStatus(): void
    {
        $remaining = $this->remainingQty();

        if ($remaining <= 0) {
            // Toàn bộ đã hỏng hoặc mất
            $this->status = 'damaged';
        } elseif ($this->available_qty <= 0) {
            // Còn hàng nhưng đang cho mượn hết
            $this->status = 'borrowed';
        } else {
            $this->status = 'available';
        }

        $this->save();
    }
}
