<!DOCTYPE html>
<html dir="{{$local=== 'ar' ? 'rtl' : 'ltr' }}" lang="{{$local ?? config('app.locale')}}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('doctor/prescription.title',[],$local) }}</title>
    @vite('resources/css/app.css')
    <style>
        #watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #watermark img {
            opacity: 0.05;
            min-width: 800px;
            min-height: 800px;
        }

        .hidden-class {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div id="divPrint" class="md:max-w-3xl mx-auto w-full flex justify-end py-2 items-end">
        <div id="print"
            class="flex items-center border-2 hover:border-black hover:text-black hover:bg-transparent text-white bg-black cursor-pointer rounded-lg p-1 gap-2">
            <span class=" font-bold md:text-lg">Imprimé</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
        </div>
    </div>
    <div class="container overflow-auto mx-auto">
        <div id="preinscription"
            class=" m-auto bg-white min-h-[90vh] relative px-12 py-8 shadow-sm overflow-hidden md:max-w-3xl">
            <div class=" flex  justify-between">
                <div class=" w-1/2 flex flex-col text-center">
                    <span class="text-lg font-bold capitalize">
                        République islamique de mauritanie
                    </span>
                    <span class=" text-sm">
                        Honneur – Fraternité – Justice
                    </span>
                </div>
                <div class=" w-1/2 text-center flex flex-col">
                    <span class="text-lg font-bold capitalize">
                        الجمهورية الإسلامية الموريتانية
                    </span>
                    <span class=" text-sm">
                        الشرف – الأخوة – العدالة
                    </span>
                </div>
            </div>
            <div id="watermark" style="pointer-events: none;">
                <img src="{{ asset('medix-logo.png') }}">
            </div>
            <div class=" flex flex-col items-center w-full pb-2 justify-center">
                <img src="{{ asset('medix-logo.png') }}" alt="{{ config('app.name') }}" class=" w-20 h-20">
                <span class=" font-bold text-lg md:text-2xl">{{ config('app.name') }}</span>
            </div>
            <div class=" flex flex-col items-center w-full pb-2 justify-center">
                <span>
                    <strong>
                        {{ $prescription->doctor->professional_title}}
                    </strong>
                    {{ $prescription->doctor->user_fullname}}
                </span>
                <span>
                    <strong>
                        {{ __('doctor/doctor.user-phone',[],$local) }} :
                    </strong>
                    <a href="tel:{{ $prescription->doctor->user->phone}}">{{ $prescription->doctor->user->phone}}</a>

                </span>
                <span>
                    <strong>
                        {{ __('doctor/doctor.user-email',[],$local) }} :
                    </strong>
                    <a href="mailto:{{ $prescription->doctor->user->email}}">{{ $prescription->doctor->user->email}}</a>
                </span>
            </div>
            <div class=" py-3">
                <div class=" flex justify-between">
                    <span>
                        {{ __('doctor/prescription.dosage',[],$local) }} : {{
                        \Carbon\Carbon::parse($prescription->created_at)->format('d/m/Y') }}
                    </span>
                    <span class=" text-lg">
                        ID : <span class=" font-bold">#{{ Str::padLeft(strval($prescription->id), 8, '0') }}</span>
                    </span>
                </div>
                <P>
                    {{ __('doctor/prescription.patient-name',[],$local) }} : {{ $prescription->patient->user_fullname }}
                </P>
                <P>
                    {{ __('doctor/doctor.user-sex',[],$local) }} : <span class=" font-bold">{{
                        $prescription->patient->user->sex
                        }}</span>
                </P>
            </div>
            <div class="md:flex">
                <div class="w-full py-2">
                    <div>
                        <table class=" w-full">
                            <thead>
                                <tr class=" border-b border-b-black">
                                    <th class=" text-start">{{ __('doctor/prescription.medicine',[],$local) }}</th>
                                    <th class=" text-start">{{ __('doctor/prescription.dosage',[],$local) }}</th>
                                    <th class=" text-start">{{ __('doctor/prescription.posologie',[],$local) }}</th>
                                    <th class=" text-start">{{ __('doctor/prescription.duration',[],$local) }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->prescription as $prescription)
                                <tr>
                                    <td class=" capitalize">
                                        {{ ($prescription['medicament']) }}
                                    </td>
                                    <td>
                                        {{ $prescription['dosage'] }}
                                    </td>
                                    <td>
                                        {{ $prescription['posologie'] }}
                                    </td>
                                    <td>
                                        {{ $prescription['duree'] }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class=" bottom-24 right-24 absolute">
                <div class=" min-w-40 border-b border-black">
                    {{ __('doctor/prescription.signature',[],$local) }} :
                </div>
            </div>
        </div>
    </div>

    <script>
        function printDiv() {
        document.getElementById('divPrint').classList.add('hidden-class');

        window.print();

        document.getElementById('divPrint').classList.remove('hidden-class');
    }
    document.getElementById('print').addEventListener('click',printDiv);
    </script>
</body>

</html>