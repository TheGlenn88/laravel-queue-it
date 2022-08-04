<?php

namespace TheGlenn88\LaravelQueueIt\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class IntegrationConfigController extends Controller
{
    public function update(Request $request)
    {
        $integrationInfoAsHex = $request->input('integrationInfo');
        $originalHash = $request->input('hash');
        $integrationInfo = hex2bin($integrationInfoAsHex);
        $generatedHash = hash_hmac('sha256', $integrationInfoAsHex, config('queueit.secret'));

        if ($generatedHash === $originalHash) {
            Cache::put('queueit:integrationconfig', $integrationInfo);
        } else {
            return response('', 400);
        }
    }
}