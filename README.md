# laravel-phone-auth

## Important
- Need laravel livewire package
- The default template uses tailwind classes (customize it if you want)

## Install

- install livewire

- install doctrine/dbal
  
- composer require lee-to/laravel-phone-auth

- php artisan vendor:publish --provider="Leeto\PhoneAuth\Providers\PhoneAuthServiceProvider"

- configure config/phone_auth.php

### Usage

### User Model

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

- Register new or login if phone verified and exist

``` html
@livewire('phone-verification', ['loginAndRegister' => true])
```

### Check phone confirmed

\Leeto\PhoneAuth\Models\ConfirmedPhone::confirmed($phone, $user_id = null);


#### Components properties (override config)
- stopEvents (bool) = turn off emitBefore, emitAfter
- customRedirectTo (bool|array) = redirect after success
- emptyCustomFields (bool) = disable custom fields
- customParams (array) = send custom properties to view

``` html
<livewire:phone-verification
    :stopEvents="true"
    :customRedirectTo="'/'"
    :emptyCustomFields="true"
    :customParams="['btn' => 'Login', 'title' => 'Login']"
    :formWrap="false"
    :loginAndRegister="true"
/>
```


