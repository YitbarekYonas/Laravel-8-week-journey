<?php

namespace App\Services;

use App\Services\Contracts\ReportGeneratorInterface;

// Stands in for something genuinely expensive to construct or rarely
// needed — imagine a PDF renderer, a heavy templating engine, or a client
// for a third-party reporting API. Most requests in a real app never
// touch reporting at all; constructing this on EVERY request regardless
// would be pure waste. That waste is exactly what deferred loading (see
// ReportServiceProvider) avoids.
class ReportGeneratorService implements ReportGeneratorInterface
{
    public function generate(): string
    {
        return 'Report generated at '.now()->toDateTimeString();
    }
}
