<?php
class Validator
{
    public static function cleanName($value)
    {
        return trim(preg_replace('/\s+/u', ' ', (string)$value));
    }

    public static function validateName($value, $label = 'Họ và tên')
    {
        $name = self::cleanName($value);
        if ($name === '') {
            throw new Exception($label . ' không được để trống.');
        }
        if (preg_match('/\d/u', $name)) {
            throw new Exception($label . ' không được chứa số.');
        }
        if (!preg_match('/^[\p{L}\s\'\.\-]+$/u', $name)) {
            throw new Exception($label . ' chỉ được nhập chữ cái.');
        }
        if (mb_strlen($name, 'UTF-8') < 2 || count(preg_split('/\s+/u', $name)) < 2) {
            throw new Exception($label . ' cần nhập đầy đủ họ và tên.');
        }
        return $name;
    }

    public static function validatePhone($value)
    {
        $phone = trim((string)$value);
        if ($phone === '') {
            throw new Exception('Số điện thoại không được để trống.');
        }
        if (!preg_match('/^[0-9]+$/', $phone)) {
            throw new Exception('Số điện thoại chỉ được nhập số.');
        }
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            throw new Exception('Số điện thoại phải đúng 10 số.');
        }
        return $phone;
    }
}
