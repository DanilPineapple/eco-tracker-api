<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class UserRegisterRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ["required"],
            "email" => ["required", "email", "unique:users"],
            "password" => ["required", "min:8", "confirmed"]
        ];
    }
}
