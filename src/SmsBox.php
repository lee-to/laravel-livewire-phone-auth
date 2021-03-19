<?php

namespace Leeto\PhoneAuth;


use Leeto\PhoneAuth\Exceptions\PhoneAuthSmsServiceNotFoundException;

/**
 * Class SmsBox
 * @package Leeto\PhoneAuth
 */
class SmsBox
{
    /**
     * @var SmsServiceInterface
     */
    protected $smsService;

    /**
     * SmsBox constructor.
     * @param SmsServiceInterface|null $smsService
     * @throws PhoneAuthSmsServiceNotFoundException
     */
    public function __construct(SmsServiceInterface $smsService = null)
    {
        if(is_null($smsService)) {
            $smsServiceClass = config("phone_auth.sms_service.class");

            if(!class_exists($smsServiceClass)) {
                throw new PhoneAuthSmsServiceNotFoundException("Service " . config("phone_auth.sms_service.class") . " not found");
            }

            $this->smsService = new $smsServiceClass();
            $this->smsService->settings(config("phone_auth.sms_service.settings"));
        }
    }

    /**
     * @return SmsServiceInterface
     */
    public function smsService() : SmsServiceInterface {
        return $this->smsService;
    }
}