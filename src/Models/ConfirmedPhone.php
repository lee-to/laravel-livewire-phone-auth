<?php

namespace Leeto\PhoneAuth\Models;

use Leeto\PhoneAuth\Casts\PhoneCast;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class ConfirmedPhone
 * @package Leeto\PhoneAuth\Models
 */
class ConfirmedPhone extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'ip', 'phone'];

    /**
     * @var string[]
     */
    protected $casts = [
        'phone' => PhoneCast::class
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function (Model $model) {
            $model->user_id = auth(config("phone_auth.auth.guard"))->check() ? auth(config("phone_auth.auth.guard"))->id() : null;
            $model->ip = request()->ip();
        });
    }


    /**
     * @param $phone
     * @param null $user_id
     * @return int
     */
    public static function confirmed($phone, $user_id = null) {
        return self::query()->where(["phone" => Str::phoneNumber($phone)])->when($user_id, function (Builder $query) use($user_id) {
            return $query->where(["user_id" => $user_id]);
        })->when(is_null($user_id), function (Builder $query) {
            return $query->where(["ip" => request()->ip()]);
        })->count();
    }

    /**
     * @return mixed
     */
    public static function flush() {
        return self::query()->where("ip", request()->ip())->delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(config("phone_auth.auth.class_user"));
    }
}
