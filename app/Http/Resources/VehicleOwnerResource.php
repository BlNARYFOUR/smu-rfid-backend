<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_vip' => $this->is_vip,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'id_number' => $this->id_number,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'owner_type' => $this->owner_type ? $this->owner_type->name : null,
            'hits' => is_null($this->AMOUNT_OF_HITS) ? 1 : $this->AMOUNT_OF_HITS,
            'created_at' => $this->created_at,
        ];
    }

    /*
     * Implemented for none eloquent pagination
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->resource);
    }
}
