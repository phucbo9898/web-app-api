<?php

namespace App\Http\Requests\User;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'image' => 'required',
            'email' => 'required|email|unique:users,email|min:3|max:255',
            'phone' => 'required|numeric',
//            'role' => auth()->user()->role == UserType::ADMIN ? 'required' : ''
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.required' => 'Ảnh đại diện người dùng không được trống',
            'name.required' => 'Tên người dùng không được trống',
            'name.min' => 'Tên người dùng ít nhất 3 kí tự',
            'name.max' => 'Tên người dùng nhiều nhất nhất 255 kí tự',
            'email.required' => 'Email không được trống',
            'email.email' => 'Địa chỉ Email không đúng',
            'email.unique' => 'Đã có người đăng kí email này',
            'email.min' => 'Email ít nhất 3 kí tự',
            'email.max' => 'Email nhiều nhất nhất 255 kí tự',
            'phone.required' => 'Số điện thoại đang để trống!, vui lòng nhập số điện thoại',
            'phone.numeric' => 'Định dạng số điện thoại không đúng !',
//            'role.required' => 'Vui lòng chọn quyền cho tài khoản đăng kí'
        ];
    }

    public function attributes()
    {
        return [

        ];
    }
}
