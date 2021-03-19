<div class="w-full my-20 text-center" wire:target="verification" wire:loading.class="opacity-50">
    @if($formWrap) <form wire:submit.prevent="verification"> @endif

        @foreach($fields as $field)
            <div>
                <input placeholder="@lang("phone_auth::phone_auth.ui.".$field."_field_label")" wire:model.defer="fieldsValues.{{ $field }}" type="text"
                       class="w-full rounded-xl mb-5 @error('fieldsValues.' . $field) border border-red-500 @enderror">
                @error('fieldsValues.' . $field)<div class="text-red-500 mb-5">{{ $message }}</div>@enderror
            </div>
        @endforeach

        @if($successfullyConfirmed)
            <input type="hidden" name="phone_verified" value="{{ $successfullyConfirmed }}" />
            <input type="hidden" name="{{ config("phone_auth.custom_phone_field_name") }}" value="{{ $phone }}" />

            <p class="text-green-500 my-5">@lang("phone_auth::phone_auth.ui.phone_verified_message")</p>
        @else

            @if($phoneVerificationCode)
                <div class="mb-5">
                    @lang("phone_auth::phone_auth.ui.current_phone") <span class="font-bold">{{ $phone }}</span>
                    <a wire:click="change" class="cursor-pointer underline">@lang("phone_auth::phone_auth.ui.change")</a>
                </div>

                <div>
                    <input placeholder="@lang("phone_auth::phone_auth.ui.code_field_label")" wire:model.defer="code" type="text"
                           class="w-full rounded-xl @error('code') border border-red-500 @enderror"
                           maxlength="{{ config("phone_auth.code_length") }}"
                    >
                    @error('code')<div class="text-red-500 mt-5">{{ $message }}</div>@enderror
                </div>

                @if($nextSend)
                    <div class="mt-5" wire:poll="nextSend">@lang("phone_auth::phone_auth.ui.next_send", ["seconds" => $nextSend])</div>
                @else
                    @if($sendCountRemain)
                        <a wire:click="send(true)" class="cursor-pointer underline mt-5">
                            @lang("phone_auth::phone_auth.ui.send_again")
                        </a>
                    @endif

                    <div class="mt-5">
                        @lang("phone_auth::phone_auth.ui.send_count_remain", ["count" => $sendCountRemain])
                    </div>
                @endif
            @else
                <div>
                    <input placeholder="@lang("phone_auth::phone_auth.ui.phone_field_label")" wire:model.defer="phone" type="tel"
                           class="w-full rounded-xl @error('phone') border border-red-500 @enderror">
                    @error('phone')<div class="text-red-500 mt-5">{{ $message }}</div>@enderror
                </div>
            @endif

            <button class="text-white block w-full mt-5 bg-blue-500 rounded-xl p-2" @if($formWrap) type="submit" @else wire:click="verification" @endif>
                {{ $phoneVerificationCode ? __("phone_auth::phone_auth.ui.check_code") : __("phone_auth::phone_auth.ui.send_code") }}
            </button>
        @endif

    @if($formWrap) </form> @endif
</div>
