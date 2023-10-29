<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserType extends Enum
{
    const ADMIN = 'admin';
    const SYSTEMADMIN = 'system_admin';
    const USER = 'user';

    public static function getUserType($role)
    {
        $typeName = [
            'admin' => __('Admin'),
            'system_admin' => __('System Admin'),
            'user' => __('User'),
        ];
        return $typeName[$role];
    }

    public static function getUserTypeName()
    {
        return [
            self::ADMIN,
            self::SYSTEMADMIN,
            self::USER
        ];
    }
}
