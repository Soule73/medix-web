<?php

namespace App\Http\Requests;

use App\Enums\User\UserSexEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PatientRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:3',
            'first_name' => 'nullable|string|max:255|min:3',
            'phone' => 'required|digits:8|unique:'.User::class,
            'avatar' => 'nullable|string|url:http,https',
            'email' => 'nullable|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required'],
            'birthday' => 'required|date|before:today|before:18 years ago',
            'city_id' => 'required|integer',
            'device_id' => 'required',
            'one_signal_id' => 'nullable',
            'sex' => ['required', 'string', Rule::enum(UserSexEnum::class)],

        ];
    }

    public function messages(): array
    {
        return [
            'birthday.before' => __('doctor/api.patient-min-age', ['min' => 18]),
            'phone.unique' => __('doctor/api.phone-number-already-exist'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.*
     *
     * @return array
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
