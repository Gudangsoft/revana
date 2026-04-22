<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$results = DB::table('submissions')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
print_r($results);
