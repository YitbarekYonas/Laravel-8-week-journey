<?php

namespace App\Services;

use App\Services\Contracts\IdGeneratorInterface;

// Holds a counter as instance state. This is the entire reason bind() vs
// singleton() matters here:
//   - bind()      → every resolution creates a NEW instance → counter
//                    always starts back at 0 → every call returns "id-1"
//   - singleton() → the container creates ONE instance, ever, and hands
//                    out that SAME instance every time it's resolved →
//                    the counter genuinely increments across calls
//
// spl_object_id() is included in next()'s output purely so the demo
// controller can PROVE whether the same object is being reused, not just
// infer it from the counter value.
class SequentialIdGenerator implements IdGeneratorInterface
{
    private int $counter = 0;

    public function next(): string
    {
        $this->counter++;

        return sprintf('id-%d (instance #%d)', $this->counter, spl_object_id($this));
    }
}
