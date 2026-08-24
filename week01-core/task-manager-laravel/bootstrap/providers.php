<?php

// Laravel 11 moved service provider registration out of config/app.php's
// 'providers' array into this dedicated file. Every provider you register
// here has its register() method called during bootstrap (to bind things
// into the container) and its boot() method called once every provider
// has been registered (Week 1 Day 3 covers the register-vs-boot
// distinction and why the order matters).
return [
    App\Providers\AppServiceProvider::class,
];
