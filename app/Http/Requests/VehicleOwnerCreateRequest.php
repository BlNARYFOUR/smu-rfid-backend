<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleOwnerCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_vip' => 'boolean',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'id_number' => 'nullable|string',
            'phone_number' => 'required|string',
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'owner_type' => 'required|numeric',
        ];
    }
}
