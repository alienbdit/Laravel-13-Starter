<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    protected $fillable   = ['key', 'value'];

    // In-memory cache so we only hit the DB once per request
    protected static ?array $cache = null;

    public static function allKeyed(): array
    {
        if (static::$cache === null) {
            static::$cache = static::query()->pluck('value', 'key')->toArray();
        }

        return static::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allKeyed()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        static::$cache = null;
    }

    public static function applyToConfig(): void
    {
        $s = static::allKeyed();

        if (! empty($s['app_name'])) {
            config(['app.name' => $s['app_name']]);
        }
        if (! empty($s['app_timezone'])) {
            config(['app.timezone' => $s['app_timezone']]);
            date_default_timezone_set($s['app_timezone']);
        }
        if (! empty($s['mail_from_name'])) {
            config(['mail.from.name' => $s['mail_from_name']]);
        }
        if (! empty($s['mail_from_address'])) {
            config(['mail.from.address' => $s['mail_from_address']]);
        }
    }
}
