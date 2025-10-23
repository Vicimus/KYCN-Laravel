<?php

namespace App\Http;

use App\Http\Middleware\AdminMiddleware;
use Symfony\Component\HttpKernel\HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        'admin' => AdminMiddleware::class,
    ];
}
