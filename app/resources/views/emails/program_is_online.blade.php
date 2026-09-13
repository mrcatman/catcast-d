@php
    $lang = new App\Helpers\LocalizationHelper();
@endphp
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style>
        @php
            echo \App\Notifications\BaseNotification::getStyles();
        @endphp
    </style>
</head>
<body>
<table class="email__table">
    <tr>
        <td class="email__logo-container">
            <img class="email__logo" src="https://catcast.tv/assets/logo-big.svg"/>
        </td>
    </tr>
    <tr>
        <td class="email__text">
            {{ LocalizationHelper::translate("notifications.types.program_is_online.text_1") }}
            <strong>{{$channel->name}}</strong> {{ LocalizationHelper::translate("notifications.types.program_is_online.text_2") }}
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%">
                <tr>
                    <td class="email__video">
                        @if (!empty($picture))
                            <img class="email__video__preview" src="{{ $picture }}"/>
                        @endif
                        <div class="email__video__texts">
                            <span class="email__video__title">{{ $title }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link"
               href="{{$url}}">  {{ LocalizationHelper::translate("notifications.types.program_is_online._button_text") }}</a>
        </td>
    </tr>
    <tr>
        <td class="email__copyright">
            (c) Catcast, 2016-2020
        </td>
    </tr>
</table>
</body>
</html>
