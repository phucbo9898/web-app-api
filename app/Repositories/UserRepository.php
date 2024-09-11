<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

//    public function query($options)
//    {
//        $query = $this->model->orderBy('created_at', 'desc');
//
//        if (Auth::user()->isSystemAdmin()) {
//            $query = $query->where('role', UserType::USER);
//        }
//
//        if (isset($options['name'])) {
//            $query = $query->where('name', 'LIKE', '%' . escape_like($options['name']) . '%');
//        }
//
//        if (isset($options['email'])) {
//            $query = $query->where('email', 'LIKE', '%' . escape_like($options['email']) . '%');
//        }
//
//        if (isset($options['role'])) {
//            $query = $query->where('role', $options['role']);
//        }
//
//        return $query;
//    }

    public function prepareUser(array $data)
    {
        $user = [
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
            'phone' => $data['phone'] ?? '',
            'avatar' => $data['image'] ?? '',
            'role' => $data['role'] ?? '',
            'status' => $data['status'] ?? ''
        ];

        return $user;
    }

    public function prepareRegister(array $data)
    {
        $user = [
            'name' => $data['name'] ?? '',
            'address' => $data['address'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => Hash::make($data['password']) ?? Hash::make(123456),
            'avatar' => $data['image'] ?? '',
            'type' => $data['type'] ?? 'user'
        ];

        return $user;
    }

    public function prepareChangePassword(array $data)
    {
        $user = [
            'password' => bcrypt($data['passwordreset'] ?? ''),
        ];

        return $user;
    }
}
