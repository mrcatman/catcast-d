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
    <tr>
        <td class="email__text">
            {{ LocalizationHelper::translate("notifications.texts.new_permission_request.text_1") }}
            <strong>{{$user->username}}</strong> {{ LocalizationHelper::translate("notifications.texts.new_permission_request.text_2") }}
        </td>
    </tr>
    <td>
        <table style="width: 100%">
            <tr>
                <td class="email__entity-info">
                    <img class="email__channel__logo" src="{{ $permission->entity_picture }}"/>
                    <span class="email__channel__texts">
                            <span class="email__channel__name">{{ $permission->entity_name }}</span>
                        </span>
                </td>
        </table>
    </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link"
               href="{{$url}}">  {{ LocalizationHelper::translate("notifications.texts.new_permission_request._button_text") }}</a>
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
