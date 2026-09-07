<?php

namespace App\Services;

use App\Services\Contracts\IdGeneratorInterface;

class SequentialIdGenerator implements IdGeneratorInterface
{
    private int $counter = 0;

    public function next(): string
    {
        $this->counter++;

        return sprintf('id-%d (instance #%d)', $this->counter, spl_object_id($this));
    }
}
