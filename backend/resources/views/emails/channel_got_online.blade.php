@extends ('emails.base')
@section('content')
    <tr>
        <td class="email__text" style="width:100%">
            <p style="text-align: center; font-size: 18px;">
                {{ $is_radio ? LocalizationHelper::translate("notifications.texts.new_broadcast.text_1_radio") : LocalizationHelper::translate("notifications.texts.new_broadcast.text_1_tv") }}
                <a href="{{$domain}}/{{$channel->shortname}}?from_notify=mail">
                    <img style="max-height: 24px" src="{{$channel->logo}}" />
                    <strong style="position: relative;top: -5px;margin-left: 5px;">{{$channel->name}}</strong></a>
                {{ $is_radio ? LocalizationHelper::translate("notifications.texts.new_broadcast.text_2_radio") :  LocalizationHelper::translate("notifications.texts.new_broadcast.text_2_tv") }}
            </p>
        </td>
    </tr>
    <tr>
        <td class="email__video">
            @if (!empty($program['picture']))
                <a href="{{$url}}">
                    <img class="email__video__preview" src="{{ $program['picture'] }}"/>
                </a>
            @endif
            <div class="email__video__texts">
                <a href="{{$url}}" class="email__video__title">{{ $program['title'] }}</a>
            </div>
            @if (!empty($program['user']))
            <div class="email__video__user">
                <a href="{{$domain}}/users/{{$program['user']->username}}?from_notify=mail">
                    <img style="max-height: 24px" src="{{$program['user']->avatar}}" />
                    <strong>{{$program['user']->username}}</strong>
                </a>
            </div>
            @endif
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link" href="{{$url}}">  {{ $is_radio ? LocalizationHelper::translate("notifications.texts.new_broadcast._button_text_radio") : LocalizationHelper::translate("notifications.texts.new_broadcast._button_text_tv") }}</a>
        </td>
    </tr>
@endsection
