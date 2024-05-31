@extends ('emails.base')
@section('content')
    <tr>
        <td class="email__text">
            <p style="text-align: left; font-size: 20px;">
                {{ LocalizationHelper::translate("notifications.types.friends_request_accepted.text_1") }}
                <a href="/users/{{$data->id}}?from_notify=mail">
                    <img style="max-height: 24px" src="{{$data->avatar}}" />
                    <strong>{{$data->username}}</strong>
                </a>
                {{ LocalizationHelper::translate("notifications.types.friends_request_accepted.text_2") }}
            </p>
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link" href="{{$url}}">  {{ LocalizationHelper::translate("notifications.types.friends_request_accepted._button_text") }}</a>
        </td>
    </tr>
@endsection
