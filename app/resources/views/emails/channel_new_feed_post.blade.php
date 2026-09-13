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
            <table style="width: 100%">
                <tr>
                    <td>
                        <img class="email__channel__logo" src="{{ $news->channel->logo }}"/>
                        <span class="email__channel__texts">
                            <span class="email__channel__name">{{ $news->channel->name }}</span>
                            <span class="email__channel__post-time">{{ $news->add_time }}</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="email__post-title">
                        {!! $news->title !!}
                    </td>
                </tr>
                <tr>
                    <td class="email__post-text">
                        {!! $news->text !!}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link"
               href="{{$url}}">  {{ LocalizationHelper::translate("notifications.types.channels_new_feed_post._button_text") }}</a>
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
