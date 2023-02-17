<?php

namespace Leeto\PhoneAuth\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class PhoneNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/^(?:\+7|7|8)+\d{10}$/", Str::phoneNumber($value));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("phone_auth::phone_auth.validation.phone_format");
    }
}
