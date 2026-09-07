<?php

namespace App\Services\Contracts;

interface ReportGeneratorInterface
{
    public function generate(): string;
}
