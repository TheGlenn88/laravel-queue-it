<?php

use Illuminate\Support\Facades\Route;
use TheGlenn88\LaravelQueueIt\Http\Controllers\IntegrationConfigController;

Route::put('/queueit/integrationconfig', [IntegrationConfigController::class, 'update']);
