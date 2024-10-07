<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserUpdateService
{
    private const BATCH_SIZE = 1000;
    private const MAX_BATCHES_PER_HOUR = 50;
    private const MAX_REQUESTS_PER_HOUR = 3600;

    private $requestsMade = 0;
    private $batchesMade = 0;
    private $lastRequestTime = 0;

    public function updateUsers(array $users): void
    {
        $batches = [];
        $batch = [];

        foreach ($users as $user) {
            $batch[] = [
                'email' => $user->email,
                'time_zone' => $user->timezone,
                'name' => $user->firstname . ' ' . $user->lastname,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $batches[] = $batch;
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $batches[] = $batch;
        }

        $this->sendBatches($batches);
    }

    private function sendBatches(array $batches): void
    {
        foreach ($batches as $batch) {
            $this->throttleRequest();
            $this->sendBatchUpdate($batch);
            $this->batchesMade++;
        }
    }

    private function sendBatchUpdate(array $batch): void
    {
        try {
            $payload = [
                'batches' => [
                    [
                        'subscribers' => $batch
                    ]
                ]
            ];

            Log::info('Batch update payload:', $payload);
        } catch (\Exception $e) {
            Log::error('Batch update failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch)
            ]);

            throw $e;
        }
    }

    public function getBatchesMade(): int
    {
        return $this->batchesMade;
    }

    private function throttleRequest(): void
    {
        $currentTime = time();
        $timeSinceLastRequest = $currentTime - $this->lastRequestTime;

        if ($this->requestsMade >= self::MAX_REQUESTS_PER_HOUR && $timeSinceLastRequest < 3600) {
            sleep(3600 - $timeSinceLastRequest);
        }

        if ($this->batchesMade >= self::MAX_BATCHES_PER_HOUR && $timeSinceLastRequest < 3600) {
            sleep(3600 - $timeSinceLastRequest);
        }

        $this->requestsMade++;
        $this->lastRequestTime = $currentTime;
    }
}
