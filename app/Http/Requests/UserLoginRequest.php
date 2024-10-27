<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class UserLoginRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email"=>["required"],
            "password"=>["required", "min:8"]
        ];
    }
}