<?php

namespace Leeto\PhoneAuth\Models\Traits;

use Illuminate\Support\Str;

/**
 * Trait PhoneVerification
 * @package Leeto\PhoneAuth\Models\Traits
 */
trait PhoneVerification
{
    /**
     * @param string $phone
     * @return bool
     */
    public static function isUniquePhone(string $phone)
    {
        return !self::query()->where("phone", Str::phoneNumber($phone))->where("phone_verified", true)->count();
    }

    /**
     * @return mixed
     */
    public function hasVerifiedPhone()
    {
        return $this->phone_verified;
    }

    /**
     * @param $phone
     * @return bool
     */
    public function setVerifiedPhone($phone)
    {
        $this->forceFill([
            'phone' => $phone,
            'phone_verified' => true,
            'phone_verified_at' => $this->freshTimestamp(),
        ]);

        return $this->save();
    }
}