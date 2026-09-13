@extends ('emails.base')
@section('content')
    <tr>
        <td class="email__text">
            <p style="text-align: center; font-size: 20px;">
                {{ LocalizationHelper::translate("notifications.types.playlists_new_recording.text_1") }}
                <a href="{{$domain}}/{{$channel->shortname}}?from_notify=mail">
                     <strong>{{$channel->name}}</strong>
                </a>
                {{ LocalizationHelper::translate("notifications.types.playlists_new_recording.text_2") }}
            </p>
        </td>
    </tr>
    <tr>
        <td class="email__video">
            @if (!empty($data->thumbnail))
                <img class="email__video__preview" src="{{ $data->thumbnail }}"/>
            @endif
            <div class="email__video__texts">
                <span class="email__video__title">{{ $data->title }}</span>
            </div>
        </td>
    </tr>
    <tr>
        <td class="email__link-container">
            <a target="_blank" class="email__link" href="{{$url}}">  {{ $is_radio ?  LocalizationHelper::translate("notifications.types.playlists_new_recording._button_text_radio")  :  LocalizationHelper::translate("notifications.types.playlists_new_recording._button_text_tv") }}</a>
        </td>
    </tr>
@endsection
