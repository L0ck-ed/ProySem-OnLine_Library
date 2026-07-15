<?php

namespace App\Config;

class Config
{
    public const HOST = 'localhost';
    public const DB = 'myprojectbiblioteca_v2';
    public const USER = 'root';
    public const PASSWORD = '';
    public const CHARSET = 'utf8mb4';

    public const BASE_URL = '/ProySem-OnLine_Library';
    public const PUBLIC_URL = self::BASE_URL . '/Public';
    public const ASSETS_URL = self::PUBLIC_URL . '/Assets';

    public static function baseUrl(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');

        if ($scriptDir === '' || $scriptDir === '.') {
            return '';
        }

        if (str_ends_with($scriptDir, '/Public')) {
            return rtrim(dirname($scriptDir), '/');
        }

        return $scriptDir === '/' ? '' : $scriptDir;
    }

    public static function publicUrl(): string
    {
        return self::baseUrl() . '/Public';
    }

    public static function assetsUrl(): string
    {
        return self::publicUrl() . '/Assets';
    }

    public static function url(string $path = ''): string
    {
        $path = trim($path, '/');
        return self::baseUrl() . ($path === '' ? '/' : '/' . $path);
    }

    public static function asset(string $path = ''): string
    {
        $path = trim($path, '/');
        return self::assetsUrl() . ($path === '' ? '' : '/' . $path);
    }
}
