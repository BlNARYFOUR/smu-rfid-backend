<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
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
            'action' => $this->action,
            'ip_address' => $this->ip_address,
            'date' => $this->created_at,
            'user' => is_null($this->user) ? [
                'id' => $this->user_id,
                'first_name' => $this->user_first_name,
                'middle_name' => $this->user_middle_name,
                'last_name' => $this->user_last_name,
            ] : [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'middle_name' => $this->user->middle_name,
                'last_name' => $this->user->last_name,
            ],
            'hits' => is_null($this->AMOUNT_OF_HITS) ? 1 : $this->AMOUNT_OF_HITS,
        ];
    }

    /*
     * Implemented for none eloquent pagination
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->resource);
    }
}
