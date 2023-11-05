<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'email' => ['required', 'email', 'min:3', 'max:255', Rule::unique('users')->ignore($this->id)],
            'phone' => 'required|numeric'
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
            'name.required' => 'Tên người dùng không được trống',
            'name.min' => 'Tên người dùng ít nhất 3 kí tự',
            'name.max' => 'Tên người dùng nhiều nhất nhất 255 kí tự',
            'email.required' => 'Email không được trống',
            'email.email' => 'Địa chỉ Email k đúng',
            'email.min' => 'Email ít nhất 3 kí tự',
            'email.max' => 'Email nhiều nhất nhất 255 kí tự',
            'email.unique' => 'Đã có người đăng kí email này',
            'phone.required' => 'Số điện thoại đang để trống!, vui lòng nhập số điện thoại !!!',
            'phone.numeric' => 'Định dạng số điện thoại không đúng !',
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('Name'),
            'image' => __('Image'),
        ];
    }
}
