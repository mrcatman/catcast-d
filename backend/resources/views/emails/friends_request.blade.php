@extends ('emails.base')
@section('content')
    <tr>
        <td class="email__text">
            <p style="text-align: left; font-size: 20px;">
                {{ LocalizationHelper::translate("notifications.texts.friends_request.text_1") }}
                <a href="/users/{{$data->id}}?from_notify=mail">
                    <img style="max-height: 24px" src="{{$data->avatar}}" />
                    <strong>{{$data->username}}</strong>
                </a>
                {{ LocalizationHelper::translate("notifications.texts.friends_request.text_2") }}
            </p>
            <ul style="text-align: left">
                @if (count($common_friends) > 0)
                    <li>
                        <strong>{{count($common_friends)}}</strong>
                        {{ LocalizationHelper::translate("notifications.texts.friends_request.text_common_friends") }}
                        <strong>
                        @php
                            echo implode(", ", (count($common_friends) > 5 ? array_slice($common_friends, 0, 5) : $common_friends))
                        @endphp
                        </strong>
                        @if (count($common_friends) > 5)
                           {{ LocalizationHelper::translate("notifications.texts.friends_request.text_common_friends_and_other")  }}
                        @endif
                    </li>
                @endif
                @if (count($common_channels) > 0)
                    <li>
                        <strong>{{count($common_channels)}}</strong>
                        {{ LocalizationHelper::translate("notifications.texts.friends_request.text_common_channels") }}
                        <strong>
                            @php
                                echo implode(", ", (count($common_channels) > 5 ? array_slice($common_channels, 0, 5) : $common_channels))
                            @endphp
                        </strong>
                        @if (count($common_channels) > 5)
                            {{ LocalizationHelper::translate("notifications.texts.friends_request.text_common_channels_and_other")  }}
                        @endif
                 @endif
            </ul>
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link" href="{{$url}}">  {{ LocalizationHelper::translate("notifications.texts.friends_request._button_text") }}</a>
        </td>
    </tr>
@endsection
