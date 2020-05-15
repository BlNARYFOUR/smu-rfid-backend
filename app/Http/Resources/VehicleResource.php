<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'model' => $this->model,
            'plate_number' => $this->plate_number,
            'or_number' => $this->or_number,
            'cr_number' => $this->cr_number,
            'licence_number' => $this->licence_number,
            'rfid_tag' => $this->rfid_tag,
            'activated_at' => $this->activated_at,
            'vehicle_type' => $this->vehicle_type ? $this->vehicle_type->name : null,
            'vehicle_owner' => new VehicleOwnerResource($this->vehicle_owner),
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
