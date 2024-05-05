<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\Doctor\DoctorStatusEnum;
use App\Http\Resources\DoctorRessource;
use App\Http\Resources\DoctorDetailRessource;
use App\Http\Resources\AllSpecialitiesRessource;
use App\Http\Resources\WorkPlacesLocationRessource;
use App\Models\WorkPlace;
use Illuminate\Http\Exceptions\HttpResponseException;

class DoctorApiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('name');

        try {
            $doctors =
                Doctor::where('status', DoctorStatusEnum::VALIDATED->value)
                ->withAvg('review_ratings', 'star')
                ->whereHas('specialities')
                ->whereHas('working_hours')
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%');
                    });
                })
                ->when($request->query('specialities_ids'), function ($query) use ($request) {
                    $query->whereHas('specialities', function ($query) use ($request) {
                        return $query->whereIn('specialities.id', json_decode($request->query('specialities_ids')));
                    });
                })
                ->when($request->query('specialityId'), function ($query) use ($request) {
                    $query->whereHas('specialities', function ($query) use ($request) {
                        return $query->where('specialities.id', '=', $request->query('specialityId'));
                    });
                })
                ->withCount(['working_hours', 'patientsCount'])
                ->having('working_hours_count', '>', 0)
                ->orderBy('review_ratings_avg_star', 'desc')
                ->orderBy('patients_count_count', 'desc')
                ->paginate(10);

            return DoctorRessource::collection(
                $doctors
            );
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json(['message' => $e->getMessage()]));
        }
    }
    public function find(Request $request, string $id)
    {
        try {
            $doctor =
                Doctor::where('status', DoctorStatusEnum::VALIDATED->value)
                ->withAvg('review_ratings', 'star')
                ->whereHas('specialities')
                ->whereHas('working_hours')
                ->withCount(['working_hours', 'patientsCount'])
                ->findOrFail($id);

            return new DoctorRessource($doctor);
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json(['message' => $e->getMessage()]));
        }
    }

    public function favoris(Request $request)
    {
        try {
            $doctors =
                Doctor::where('status', DoctorStatusEnum::VALIDATED->value)
                ->withAvg('review_ratings', 'star')
                ->whereIn('id', json_decode($request->query('doctorsId')))
                ->withCount(['working_hours', 'patientsCount'])
                ->having('working_hours_count', '>', 0)
                ->orderBy('review_ratings_avg_star', 'desc')
                ->orderBy('patients_count_count', 'desc')
                ->paginate();

            return DoctorRessource::collection(
                $doctors
            );
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json(['message' => $e->getMessage()]));
        }
    }

    public function workPlcaLocation(Request $request)
    {
        // Coordonnées de l'utilisateur
        $userLatitude = $request->query('userLatitude');
        $userLongitude = $request->query('userLongitude');
        if ($userLatitude && $userLongitude) {
            $radius_of_the_earth = 6371; //représente le rayon moyen de la Terre en kilomètres
            $nearestPlaces = WorkPlace
                ::selectRaw("*,
            ($radius_of_the_earth * acos(cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude)))) AS distance", [$userLatitude, $userLongitude, $userLatitude])
                ->havingRaw('distance < ?', [50]) // Vous pouvez ajuster la distance maximale ici
                ->orderBy('distance', 'asc')
                ->get();

            return WorkPlacesLocationRessource::collection($nearestPlaces);
        }
        return [];
    }

    public function show(Request $request, string $id)
    {

        try {
            $doctor = Doctor::where('status', DoctorStatusEnum::VALIDATED->value)
                ->with([
                    'review_ratings' => function ($query) {
                        $query->with('patient')->limit(10);
                    },
                    'working_hours' => function ($query) {
                        $query->with([
                            'work_place' => function ($query) {
                                $query->select('id', 'name', 'address', 'latitude', 'longitude');
                            },
                            'day',
                        ]);
                    },
                ])
                ->findOrFail($id);

            $groupedWorkingHours = $doctor->working_hours->groupBy(function ($item) {
                return $item->day->name;
            });

            $doctor->grouped_working_hours = $groupedWorkingHours;

            unset($doctor->working_hours);

            // return $doctor;
            return new DoctorDetailRessource($doctor);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    public function specialities(Request $request)
    {
        try {
            $specialities = Speciality::all();

            return response()->json(AllSpecialitiesRessource::collection($specialities));
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
}
