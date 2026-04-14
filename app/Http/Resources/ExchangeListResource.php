<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use App\Enums\ExchangeStatus;
use App\Models\Exchange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inputAmount' => $this->input_amount,
            'outputAmount' => rtrim(rtrim($this->output_amount, '0'), '.'),
            'outputCurrency' => $this->output_currency,
            'status' => ExchangeStatus::getName($this->status),
            'walletNumber' => $this->wallet_number,
        ];
    }
}
