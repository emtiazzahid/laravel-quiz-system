@component('mail::message')
# Daily Digest Report

This is a digest report of your Quiz

## Title: {{ $report['quiz_title']  }}

## Date: {{ $report['date']  }}

@component('mail::table')
    |#     | User                          | Attempts | AVG  | MAX | MIN
    | ---- | :---------------------------- | :------: | :--: | :-: | :--: |
    @foreach($report['users'] as $user)
    |{{ $loop->iteration }}|[{{ $user['user_name'] }}](mailto::{{ $user['user_email'] }})|{{ $user['number_of_attempt'] }}|{{ $user['avg'] }}|{{ $user['max'] }}|{{ $user['min'] }}
    @endforeach

@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
