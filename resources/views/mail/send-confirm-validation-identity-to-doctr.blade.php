@component('mail::message')
# {{ $title }}

@component('mail::panel')
{{__('doctor/notification.good-morning').$doctorFullName }}
@endcomponent

{{ $body }}

@component('mail::button', ['url' => $url])
{{ __('doctor/notification.log-in-to-your-space') }}
@endcomponent

{{ __('doctor/notification.thanks') }},<br>
{{ config('app.name') }}
@endcomponent