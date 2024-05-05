<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentRessource;
use App\Jobs\SendNotificationToDoctorPatient;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Jobs\SendConfirmRescheduleDateNotificationToDoctor;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments by patient.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $appointment = Appointment::where('patient_id', $request->user()->patient->id)
                ->with('doctor')
                ->when($request->query('status'), function ($query) use ($request) {
                    $query->where('status', $request->query('status'));
                })
                ->orderBy('date_appointment', 'desc')
                ->paginate();

            return AppointmentRessource::collection($appointment);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $doctorId = $request->doctor_id;
            $doctor = Doctor::findOrFail($doctorId);

            $data = [
                ...$request->validated(),
                'amount' => $doctor->visit_price,
            ];

            $newAppointment = Appointment::create($data);
            $newAppointment->save();
            SendNotificationToDoctorPatient::dispatch(appointment: $newAppointment);

            return response()->json(new AppointmentRessource(Appointment::find($newAppointment->id)));
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * Display the specified appointment by id.
     *
     * @param  string|int  $appointmentId
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $appointment = Appointment::where('patient_id', '=', $request->user()->patient->id)->findOrFail($id);

            return response()->json(new AppointmentRessource($appointment));
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * Update the specified appointment by id in storage.
     *
     * @param  string|int  $id
     */
    public function update(UpdateAppointmentRequest $request, string $id): JsonResponse
    {
        Appointment::findOrFail($id)->update($request->validated());

        return response()->json(new AppointmentRessource(Appointment::find($id)));
    }

    /**
     * Remove the specified appointment by id from storage.
     *
     * @param  string|int  $id
     */
    public function destroy(string $id): JsonResponse
    {
        Appointment::findOrFail($id)->delete();

        return response()->json('', 204);
    }

    /**
     * confirm the specified appointment reschedule date by id from storage.
     *
     * @param  string|int  $id
     */
    public function confirmAppoinment(string $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $rescheduleDate = $appointment->reschedule_date;
        // $addByDoctor = $appointment->add_by_doctor;
        if ($rescheduleDate) {
            $appointment->date_appointment = $rescheduleDate;
            $appointment->reschedule_date = null;
            $appointment->save();
        }
        SendConfirmRescheduleDateNotificationToDoctor::dispatch(appointment: $appointment);

        return response()->json(new AppointmentRessource(Appointment::find($id)));
    }
}
