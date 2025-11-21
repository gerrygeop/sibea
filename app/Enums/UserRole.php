<?php

namespace App\Enums;

class UserRole
{
    public const ADMIN_ID = 1;
    public const STAFF_ID = 2;
    public const MAHASISWA_ID = 3;
    public const PENGELOLA_ID = 4;

    public const ADMIN = 'admin';
    public const STAFF = 'staff';
    public const MAHASISWA = 'mahasiswa';
    public const PENGELOLA = 'pengelola';

    public static function toOptions(): array
    {
        return [
            self::ADMIN_ID => self::ADMIN,
            self::STAFF_ID => self::STAFF,
            self::MAHASISWA_ID => self::MAHASISWA,
            self::PENGELOLA_ID => self::PENGELOLA,
        ];
    }
}
