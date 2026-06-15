<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGatewaySetting extends Model
{
    protected $fillable = [
        'provider', 'api_key', 'api_secret', 'from_number',
        'endpoint_url', 'extra_params', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'extra_params' => 'array',
            'is_enabled'   => 'boolean',
        ];
    }

    public static function current(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'provider'   => 'custom',
            'is_enabled' => false,
        ]);
    }
}
