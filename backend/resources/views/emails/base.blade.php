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
    @yield('content')
    <tr>
        <td class="email__copyright">
            (c) Catcast, 2016-2020
        </td>
    </tr>
</table>
</body>
</html>
