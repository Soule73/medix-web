<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTokenRequest extends FormRequest
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
            'phone' => 'required',
            'password' => 'required',
            'device_id' => 'required',
        ];
    }
    public function messages(): array
    {
        return [
            'phone.required' => __('doctor/api.phone-required'),
            'password.required' => __('auth.password'),
            'device_id.required' => __('doctor/api.somethin-weng-wrong')
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errorMessage = $validator->errors()->all()[0];
        throw new HttpResponseException(response()->json($errorMessage, 422));
    }
}
