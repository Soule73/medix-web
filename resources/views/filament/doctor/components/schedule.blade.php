<!DOCTYPE html>
<html dir="{{$local=== 'ar' ? 'rtl' : 'ltr' }}" lang="{{$local ?? config('app.locale')}}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('doctor/relation/working-hour.timetable',[], $local ?? config('app.locale')) }}</title>
    @if ($local==="ar")
    <style>
        * {
            /* font-family:
                'DejaVu Sans', sans-serif; */
            font-family: "rubik", sans-serif;

        }
    </style>
    @else
    <style>
        #watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            /* opacity: 0.5; */
            /* Adjust the opacity as needed */
        }

        #watermark img {
            opacity: 0.1;
            widows: 800px;
            height: 800px;
            /* 50% transparency */
        }
    </style>
    @endif
    <style>
        table .th-td-default,
        table .td-have-content {
            border: 1px solid #000;
        }

        .th-td-default {

            padding: 0.25rem;
        }

        .td-have-content {
            background-color: #c6c8cc;
            padding: 0.25rem;
            text-align: center;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight:
                700;
        }

        table th,
        table td {
            min-width: 7rem;
        }

        .td-empty {
            border-left: 1px solid #000;
            border-right: 1px solid #000;


        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            color: #675757;
            font-size: 0.75rem;
            font-weight: 700;
        }

        table {

            border-collapse: collapse;
            margin: auto;
            border: 1px solid #000;
            @if($local==='ar') width: 80% @endif
        }

        .application-logo {
            height: 5rem;
            width: 5rem;
            margin: auto auto -19 auto;
        }

        .author-info {
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    @if($local!=="ar")
    <div id="watermark">
        <img src="{{ base_path('resources/assets/medix-logo.png') }}">
    </div>
    @endif
    <table>
        <caption>
            {{-- application logo --}}
            <img src="{{ base_path('resources/assets/medix-logo.png')}}" alt="{{ config('app.name') }}"
                class="application-logo">
            {{-- end application logo --}}
            <p class="author-info">
                <u>

                    {{ __('doctor/relation/working-hour.timetable',[], $local ?? config('app.locale')) }}
                </u>
            </p>
            {{$user->doctor->professional_title.' '.$user->first_name.' '.$user->name }}
        </caption>
        <thead>
            <tr>
                <th class="th-td-default">
                    {{__('doctor/relation/working-hour.hour',[], $local ?? config('app.locale')) }}</th>
                @foreach ($days as $day)
                <th class="th-td-default">
                    {{__("day.$day->name",[], $local ?? config('app.locale')) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($hours as $index => $hour)
            <tr>
                <td class="th-td-default">
                    {{ $hour }} -
                    {{ Carbon\Carbon::createFromTimeString($hour)->addHours(1)->format('H:i') }}
                </td>
                @foreach ($days as $day)
                @php
                $working_hour = $day->working_hours->first(fn($wh) => $hour >= substr($wh->start_at, 0, 5) && $hour
                < substr($wh->end_at, 0, 5));
                    @endphp
                    @if ($working_hour && $index === $hours->search(substr($working_hour->start_at, 0, 5)))
                    <td class="td-have-content" rowspan="{{ $working_hour->getRowCount($hours) }}">
                        {{ $working_hour->work_place->name }}
                    </td>
                    @elseif (!$day->working_hours->contains(fn($wh) => $hour >= substr($wh->start_at, 0, 5)
                    && $hour < substr($wh->end_at, 0, 5)))
                        <td class="td-empty">
                            @if ($local==="ar")
                            <span style="visibility: hidden;">
                                ____________</span>
                            @endif
                        </td>
                        @endif
                        @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    {{--- copyright ---}}
    <div class="copyright">
        &copy;{{ \Carbon\Carbon::now()->format('Y') }} {{ config('app.name') }} | {{
        __('doctor/doctor.all-rights-reserved',[], $local ?? config('app.locale')) }}
    </div>
    {{--- end copyright ---}}
</body>

</html>