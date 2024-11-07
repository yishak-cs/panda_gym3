<?php

namespace App\DTO;

use Carbon\Carbon;

class GymAccessPayload
{
    public function __construct(
        public readonly int $memberId,
        public readonly int $subscriptionId,
        public readonly string $membershipName,
        public readonly Carbon $endDate,
        public readonly Carbon $timestamp
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['memberId'],
            $data['subscriptionId'],
            $data['membershipName'],
            $data['endDate'],
            $data['timestamp']
        );
    }

    public function toArray(): array
    {
        return [
            'memberId' => $this->memberId,
            'subscriptionId' => $this->subscriptionId,
            'membershipName' => $this->membershipName,
            'endDate' => $this->endDate,
            'timestamp' => $this->timestamp,
        ];
    }
}
