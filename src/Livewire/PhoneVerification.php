<?php

namespace Leeto\PhoneAuth\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Leeto\PhoneAuth\Exceptions\PhoneAuthLimitException;
use Leeto\PhoneAuth\Models\ConfirmedPhone;
use Leeto\PhoneAuth\Models\PhoneVerificationCode;
use Leeto\PhoneAuth\Rules\PhoneNumber;

use App\Models\User;

use Livewire\Component;

/**
 * Class PhoneVerification
 * @package Leeto\PhoneAuth\Livewire
 */
class PhoneVerification extends Component
{
    /**
     * @var bool
     */
    public $formWrap = true;

    /**
     * @var bool
     */
    public $loginAndRegister = false;

    /**
     * @var
     */
    public $phone;


    /**
     * @var
     */
    public $confirmedPhone;

    /**
     * @var
     */
    public $code;

    /**
     * @var
     */
    public $phoneVerificationCode;

    /**
     * @var
     */
    public $nextSend;


    /**
     * @var
     */
    public $sendCountRemain;

    /**
     * @var
     */
    public $successfullyConfirmed = false;

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    public $fieldsValues = [];

    /**
     * @var array
     */
    public $customFields = [];

    /**
     * @var boolean
     */
    public $emptyCustomFields = false;

    /**
     * @var array
     */
    public $customFieldsRules = [];

    /**
     * @var array
     */
    public $customParams = [];

    /**
     * @var string
     */

    public $customRedirectTo = "";

    /**
     * @var boolean
     */
    public $stopEvents = false;

    /**
     * @var array
     */
    protected $listeners = [
        'phoneVerification' => 'verification',
        'phoneVerificationDefaultParams' => 'defaultParams'
    ];

    /**
     * @return array
     */
    protected function rules()
    {
        $rules = [
            'phone' => ['required', new PhoneNumber()],
            'code' => ['nullable', 'sometimes']
        ];

        $customFieldsRules = $this->customFieldsRules ? collect($this->customFieldsRules) : collect(config("phone_auth.custom_fields_rules"));

        if($this->emptyCustomFields) {
            $customFieldsRules = collect([]);
        }

        $newRules = $customFieldsRules->mapWithKeys(function ($value, $key) {
            return ["fieldsValues.{$key}" => $value];
        })->merge($rules);



        return $newRules->toArray();
    }

    /**
     *
     */
    public function mount() {
        $customFields = $this->customFields ? $this->customFields : config("phone_auth.custom_fields", []);

        if($this->emptyCustomFields) {
            $customFields = [];
        }

        $this->fields = $this->fields ? $this->fields : $customFields;

        $this->sendCountRemain();
    }

    public function defaultParams($params) {
        $this->fieldsValues = collect($this->fieldsValues)->merge($params)->toArray();
    }

    /**
     *
     */
    public function nextSend() {
        $this->nextSend = PhoneVerificationCode::nextSend();
    }

    /**
     *
     */
    public function sendCountRemain() {
        $this->sendCountRemain = PhoneVerificationCode::sendCountRemain();
    }

    /**
     * @param false $resend
     */
    public function send($resend = false) {
        try {
            $this->phoneVerificationCode = PhoneVerificationCode::sendCode($this->phone);

            $this->nextSend();
            $this->sendCountRemain();

            $this->resetErrorBag();
            $this->resetValidation();

            $this->eventsSendCode();
        } catch (PhoneAuthLimitException $e) {
            $this->addError($resend ? "code" : "phone", $e->getMessage());
        }
    }

    /**
     *
     */
    public function change() {
        $this->reset(["phone", "code", "successfullyConfirmed", "phoneVerificationCode"]);
    }

    /**
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function getAuth() {
        return auth(config("phone_auth.auth.guard"));
    }

    /**
     * @param $value
     */
    public function updatingCode($value) {
        $value = Str::of($value)->replace(" ", "");

        if(config("phone_auth.verify_code_dynamic") && Str::length($value) == config("phone_auth.code_length")) {
            $this->code = $value;

            $this->dispatchBrowserEvent('code-loading');

            $this->verification();
        } else {
            $this->resetErrorBag(["code"]);
            $this->resetValidation(["code"]);
        }
    }

    /**
     * @param $value
     */
    public function updatingPhone($value) {
        if($this->successfullyConfirmed && $this->confirmedPhone != $value) {
            $this->reset(["code", "confirmedPhone", "successfullyConfirmed", "phoneVerificationCode"]);
        }
    }

    /**
     *
     */
    public function verification() {
        if($this->successfullyConfirmed) {
            $this->eventsAfter();
        } else {
            $this->dispatchBrowserEvent('phone-verification');

            $this->eventsBefore();

            $this->validate();

            if($this->loginAndRegister == false && !User::isUniquePhone($this->phone)) {
                $this->addError("phone", __("phone_auth::phone_auth.validation.phone_already_use"));

                if($this->phoneVerificationCode) {
                    $this->change();
                }
            } else {
                if($this->phoneVerificationCode) {
                    if(PhoneVerificationCode::validateCode($this->code)) {
                        try {
                            if(config("phone_auth.auth.createUser")) {
                                $userModel = app(config("phone_auth.auth.class_user"));

                                $fields = collect($this->fields)->mapWithKeys(function ($item) {
                                    return [$item => $this->fieldsValues[$item] ?? ''];
                                });

                                $formatPhoneNumber = Str::phoneNumber($this->phone);

                                $createData = Arr::add($fields->toArray(), "phone", $formatPhoneNumber);

                                $user = $this->getAuth()->check() ? $this->getAuth()->user() : $userModel->firstOrCreate(["phone" => $formatPhoneNumber], $createData);
                                $user->setVerifiedPhone($this->phone);

                                if(config("phone_auth.auth.loginAfter") && !$this->getAuth()->check()) {
                                    $this->getAuth()->login($user);
                                }

                                ConfirmedPhone::firstOrCreate(["phone" => $this->phone]);
                            }

                            $this->phone = $this->phoneVerificationCode->phone;
                            $this->successfullyConfirmed = true;
                            $this->confirmedPhone = $this->phone;

                            $this->eventsAfter();

                            $this->reset(["code", "sendCountRemain", "phoneVerificationCode", "nextSend"]);

                            if(config("phone_auth.flushCode")) {
                                PhoneVerificationCode::flush();
                            }

                            $this->dispatchBrowserEvent('phone-verification-done');

                            $redirectTo = $this->customRedirectTo ? $this->customRedirectTo : config("phone_auth.auth.redirectTo");

                            if($redirectTo) {
                                $this->redirect($redirectTo);
                            }
                        } catch (\Exception $e) {
                            dd($e->getMessage());
                            $this->addError("code", __("phone_auth::phone_auth.validation.error"));
                        }
                    } else {
                        $this->addError("code", __("phone_auth::phone_auth.validation.code_invalid"));
                    }
                } else {
                    $this->send();
                }
            }
        }
    }

    /**
     *
     */
    protected function eventsSendCode() {
        if(!$this->stopEvents && config("phone_auth.emitSendCode")) {
            $this->emit(config("phone_auth.emitSendCode"), [
                "phone" => Str::phoneNumber($this->phone),
                "values" => $this->fieldsValues,
            ]);
        }
    }

    /**
     *
     */
    protected function eventsBefore() {
        if(!$this->stopEvents && config("phone_auth.emitBefore")) {
            $this->emit(config("phone_auth.emitBefore"), [
                "success" => $this->successfullyConfirmed,
                "phone" => Str::phoneNumber($this->phone),
                "values" => $this->fieldsValues,
            ]);
        }
    }

    /**
     *
     */
    protected function eventsAfter() {
        if(!$this->stopEvents && config("phone_auth.emitAfter")) {
            $this->emit(config("phone_auth.emitAfter"), [
                "success" => $this->successfullyConfirmed,
                "phone" => Str::phoneNumber($this->phone),
                "values" => $this->fieldsValues,
            ]);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('phone_auth::livewire.phone-verification');
    }
}
