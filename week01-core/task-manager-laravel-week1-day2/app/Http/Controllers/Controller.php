<?php

namespace App\Http\Controllers;

// Every controller extends this. Empty for now — Laravel 11 dropped the
// AuthorizesRequests/ValidatesRequests traits from the base class by
// default (they're opt-in per-controller now), so this really is just a
// shared marker/base class until a project needs something common across
// every controller.
abstract class Controller
{
    //
}
