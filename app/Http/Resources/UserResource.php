<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

 class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'attributes' => [
                // 'cognitoId'                 => $this->resource->cognitoId,
                'name'                      => $this->resource->name,
                'lastname'                  => $this->resource->lastname,
                'email'                     => $this->resource->email,
                'status_user'               => $this->resource->deleted_at ? false : true,
                'code_user'                 =>$this->resource->code_user,
                'email_verified_at'         => $this->resource->email_verified_at,
                // 'deleted_at'                => $this->resource->deleted_at,
                 'created_at'                => $this->resource->created_at,
                // 'updated_at'                => $this->resource->updated_at,

            ],
            'relationships' => [
                'userDetail' => $this->whenLoaded('userDetail', function() {
                    return UserDetailResource::make($this->resource->userDetail);
                }),
                'roles' => $this->whenLoaded('roles', function() {
                    return RoleResource::collection($this->resource->roles);
                }),
                'bankAccounts' => $this->whenLoaded('bankAccounts', function() {
                    return BankAccountResource::collection($this->resource->bankAccounts);
                }),
                'financing' => $this->whenLoaded('financing', function() {
                    return FinancingResource::make($this->resource->financing);
                })
            ],
        ];
    }
}
