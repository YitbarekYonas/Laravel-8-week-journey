<?php

namespace App\Services;

use App\Services\Contracts\ReportGeneratorInterface;

class ReportGeneratorService implements ReportGeneratorInterface
{
    public function generate(): string
    {
        return 'Report generated at '.now()->toDateTimeString();
    }
}
