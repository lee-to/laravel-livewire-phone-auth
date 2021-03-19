# laravel-phone-auth

## Imporant
- Need laravel livewire package
- The default template uses tailwind classes (customize it if you want)

## Install

- install livewire
  
- composer require lee-to/laravel-phone-auth

- php artisan vendor:publish --provider="Leeto\PhoneAuth\Providers\PhoneAuthServiceProvider"

- configure config/phone_auth.php

### Usage

### Blade component

- Add PhoneVerification Trait to User Model

``` php
use PhoneVerification;
```

- Add phone cast to User Model

``` php
protected $casts = [
    'phone' => PhoneCast::class
];
```

### Blade component
#### Auth/Phone verification form

- Simple
``` html
@livewire('phone-verification')
```

- Without form wrap

``` html
@livewire('phone-verification', ['formWrap' => false])
```

- Regsiter new or login if phone verified and exist

``` html
@livewire('phone-verification', ['loginAndRegister' => true])
```

### Check phone confirmed

\Leeto\PhoneAuth\Models\ConfirmedPhone::confirmed($phone, $user_id = null);


