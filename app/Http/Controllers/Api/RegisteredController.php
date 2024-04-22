<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRegistrationRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class RegisteredController extends Controller
{
    /**
     * creation d'un utilisateur et associer un profile patient
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(PatientRegistrationRequest $request)
    {
        try {

            // Récupérez toutes les données validées
            $validatedData = $request->validated();

            // Hachez le mot de passe et remplacez-le
            //dans le tableau des données validées
            $validatedData['password'] = Hash::make($request->password);

            // Valider le numéro de téléphone
            // (une logique de vérification doit être fait à partir
            // l'application mobile avec flutter firebase authentifcation par mobile)
            $validatedData['phone_verified_at'] = now();

            // Créez l'utilisateur avec les données validées
            $user = User::create($validatedData);

            // Associez le patient à l'utilisateur
            //et créez l'enregistrement avec les données supplémentaires
            $user->patient()->create([
                'city_id' => $request->city_id,
                //Pour s'ssurez que la date d'anniversaire est
                //dans un format acceptable pour la base de données
                'birthday' => Carbon::parse($request->birthday)->format('Y-m-d'),
            ]);

            // retourner le token la valeur $device_id doit être
            //valide pour l'identification de chaque appareil
            $token = $user->createToken($request->device_id)->plainTextToken;

            return response()
                ->json([
                    'token' => $token,
                    'status' => 'success',
                ]);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
}
