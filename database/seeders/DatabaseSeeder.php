<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder - Tạo dữ liệu mẫu
 *
 * Chạy: php artisan db:seed
 * Hoặc: php artisan migrate --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== TẠO TÀI KHOẢN ADMIN =====
        User::updateOrCreate(
            ['email' => 'admin@qltb.local'],
            [
                'name'     => 'Quản Trị Viên',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'phone'    => '0901234567',
                'school'   => 'Trường THPT ABC',
                'status'   => 'active',
            ]
        );

        // ===== TẠO GIÁO VIÊN MẪU =====
        User::updateOrCreate(
            ['email' => 'teacher@qltb.local'],
            [
                'name'     => 'Nguyễn Văn An',
                'password' => Hash::make('password'),
                'role'     => 'teacher',
                'phone'    => '0987654321',
                'school'   => 'Trường THPT ABC',
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'teacher2@qltb.local'],
            [
                'name'     => 'Trần Thị Bích',
                'password' => Hash::make('password'),
                'role'     => 'teacher',
                'phone'    => '0912345678',
                'school'   => 'Trường THPT ABC',
                'status'   => 'active',
            ]
        );

        // ===== TẠO THIẾT BỊ MẪU =====
        $devices = [
            ['code' => 'TB001', 'name' => 'Máy tính xách tay Dell',    'category' => 'Máy tính',    'quantity' => 10, 'available_qty' => 10],
            ['code' => 'TB002', 'name' => 'Máy chiếu Epson',           'category' => 'Máy chiếu',   'quantity' => 5,  'available_qty' => 5],
            ['code' => 'TB003', 'name' => 'Loa Bluetooth JBL',         'category' => 'Âm thanh',    'quantity' => 8,  'available_qty' => 8],
            ['code' => 'TB004', 'name' => 'Micro không dây',           'category' => 'Âm thanh',    'quantity' => 6,  'available_qty' => 6],
            ['code' => 'TB005', 'name' => 'Màn hình chiếu 120 inch',   'category' => 'Máy chiếu',   'quantity' => 3,  'available_qty' => 3],
            ['code' => 'TB006', 'name' => 'Máy ảnh Canon EOS',         'category' => 'Máy ảnh',     'quantity' => 4,  'available_qty' => 4],
            ['code' => 'TB007', 'name' => 'Bảng thông minh tương tác', 'category' => 'Thiết bị dạy học', 'quantity' => 2, 'available_qty' => 2],
            ['code' => 'TB008', 'name' => 'Máy in HP LaserJet',        'category' => 'Máy in',      'quantity' => 3,  'available_qty' => 3],
        ];

        foreach ($devices as $device) {
            Device::updateOrCreate(
                ['code' => $device['code']],
                array_merge($device, ['status' => 'available'])
            );
        }

        $this->command->info('✅ Đã cập nhật dữ liệu mẫu thành công!');
    }
}
