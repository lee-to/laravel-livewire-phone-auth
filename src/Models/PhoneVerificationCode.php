<?php

namespace Leeto\PhoneAuth\Models;

use Leeto\PhoneAuth\Casts\PhoneCast;
use Leeto\PhoneAuth\Exceptions\PhoneAuthLimitException;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class PhoneVerificationCode
 * @package Leeto\PhoneAuth\Models
 */
class PhoneVerificationCode extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'ip', 'phone', 'code', 'expires_at'];

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
            $code = Str::upper(Str::random(config("phone_auth.code_length")));

            $model->user_id = self::getUserId();
            $model->ip = self::getIp();
            $model->code = $code;
            $model->expires_at = now()->addSeconds(config("phone_auth.expire_seconds"));

            app("sms")->send($model->phone, __("phone_auth::phone_auth.sms.text", ["code" => $code]));
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnexpired(Builder $query): Builder
    {
        return $query->where('ip', self::getIp())->where('expires_at', '>=', now());
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeLimiter(Builder $query): Builder
    {
        return $query->where('ip', self::getIp())->whereDate('expires_at', '=', now());
    }

    /**
     * @return float|int
     */
    public static function nextSend() {
        $nexSendTimestamp = self::query()->unexpired()->max('created_at');

        $nexSendTimestamp = $nexSendTimestamp ? now()->setDateTimeFrom($nexSendTimestamp)->addSeconds(config("phone_auth.next_send_after")) : now();

        return $nexSendTimestamp->isPast() ? 0 : now()->diffInSeconds($nexSendTimestamp);
    }

    /**
     * @return mixed
     */
    public static function sendCountRemain() {
        $sendCount = config("phone_auth.limit_send_count") - self::query()->limiter()->count();

        return $sendCount < 0 ? 0 : $sendCount;
    }

    /**
     * @return mixed
     */
    public static function sendCount() {
        return self::query()->limiter()->count();
    }

    /**
     * @param $phone
     * @return mixed
     * @throws PhoneAuthLimitException
     */
    public static function sendCode($phone) {
        $nextSend = self::nextSend();
        $sendCount = self::sendCount();

        if($nextSend > 0) {
            throw new PhoneAuthLimitException(__("phone_auth::phone_auth.ui.sms_count_limit", ["seconds" => $nextSend]));
        }

        if(config("phone_auth.limit_send_count") > $sendCount) {
            return self::create(["phone" => $phone]);
        } else {
            throw new PhoneAuthLimitException(__("phone_auth::phone_auth.ui.send_count", ["count" => config("phone_auth.limit_send_count")]));
        }
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function validateCode($code) {
        return self::query()->unexpired()->where("code", $code)->count();
    }

    /**
     * @return null|string
     */
    protected static function getIp() {
        return request()->ip();
    }

    /**
     * @return int|null|string
     */
    protected static function getUserId() {
        return auth(config("phone_auth.auth.guard"))->check() ? auth(config("phone_auth.auth.guard"))->id() : null;
    }

    /**
     * @return mixed
     */
    public static function flush() {
        return self::query()->where("ip", self::getIp())->delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(config("phone_auth.class_user"));
    }
}
