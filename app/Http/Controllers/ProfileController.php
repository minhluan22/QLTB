<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang thông tin cá nhân hiện đại
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone'            => ['nullable', 'string', 'max:20'],
            'subject_group'    => ['nullable', 'string', 'max:255'],
            'teaching_subject' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'name.required' => 'Họ và tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được sử dụng.',
            'avatar.image' => 'Ảnh đại diện phải là một tệp hình ảnh.',
            'avatar.mimes' => 'Hỗ trợ các định dạng jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'Kích thước ảnh không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin thành công!');
    }

    /**
     * Cập nhật mật khẩu cá nhân
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', \Illuminate\Validation\Rules\Password::min(6), 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.required' => 'Mật khẩu mới không được để trống.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật mật khẩu thành công!');
    }
}
