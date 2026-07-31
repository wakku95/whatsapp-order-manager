<?php

namespace App\Contracts\AI;

interface AIServiceInterface
{
    /**
     * Parse raw customer message into structured intent data.
     *
     * @param string $message
     * @return array Standardized structured array containing intent and items
     */
    public function parseIntent(string $message): array;
}
