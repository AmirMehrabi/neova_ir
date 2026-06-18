<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OtpCode extends Model
{
    protected $fillable = ['phone', 'code', 'expires_at', 'used'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }

    public static function generate(string $self): string
    {
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        static::where('phone', $self)->where('used', false)->update(['used' => true]);

        static::create([
            'phone' => $self,
            'code' => $code,
            'expires_at' => now()->addMinutes(3),
        ]);

        return $code;
    }

    public static function verify(string $phone, string $code): bool
    {
        $otp = static::where('phone', $phone)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['used' => true]);
        return true;
    }
}
