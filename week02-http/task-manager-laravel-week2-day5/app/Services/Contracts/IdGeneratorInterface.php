<?php

namespace App\Services\Contracts;

interface IdGeneratorInterface
{
    public function next(): string;
}
