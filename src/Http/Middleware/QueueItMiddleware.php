<?php

namespace TheGlenn88\LaravelQueueIt\Http\Middleware;

use Closure;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use QueueIT\KnownUserV3\SDK\KnownUser;

class QueueItMiddleware
{
    public function handle($request, Closure $next)
    {
        $customerID = config('queueit.customer_id');
        $secretKey = config('queueit.secret');

        // Check required environment variables set or continue with response.
        if (is_null($customerID) || is_null($secretKey) || $request->route()->uri === 'health-check') {
            $response = $next($request);
            return $response;
        }

        $configText = Cache::get('queueit:integrationconfig');

        // If configuration doesn't exist, go and fetch it from Queue-It and cache it.
        if (is_null($configText)) {
            $apiKey = config('queueit.api_key');
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'api-key' => $apiKey
                ])->retry(3, 1000)->get("https://{$customerID}.queue-it.net/status/integrationconfig/secure/{$customerID}");
            } catch (GuzzleException $e) {
                $response = $next($request);
                return $response;
            }

            $configText = $response->body();
            Cache::put('queueit:integrationconfig', $configText);
        }

        $queueittoken = $request->input('queueittoken');

        $fullUrl = \URL::full();
        $currentUrlWithoutQueueitToken = preg_replace("/([\\?&])(" . "queueittoken" . "=[^&]*)/i", "", $fullUrl);

        //Verify if the user has been through the queue
        $result = KnownUser::validateRequestByIntegrationConfig(
            $currentUrlWithoutQueueitToken,
            $queueittoken,
            $configText,
            $customerID,
            $secretKey
        );

        if ($result->doRedirect()) {
            // Adding no cache headers to prevent browsers to cache requests
            $noCacheHeaders = [
                'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ];
            //end

            if (!$result->isAjaxResult) {
                //Send the user to the queue - either because hash was missing or because it was invalid
                return redirect($result->redirectUrl, 302, $noCacheHeaders);
            } else {
                $ajaxResultHeaders = [
                    'Access-Control-Expose-Headers' => $result->getAjaxQueueRedirectHeaderKey(),
                    $result->getAjaxQueueRedirectHeaderKey() => $result->getAjaxRedirectUrl(),
                ];
                return response('', 200, array_merge($noCacheHeaders, $ajaxResultHeaders));
            }
        }
        if (!empty($queueittoken) && $result->actionType == "Queue") {
            return redirect($currentUrlWithoutQueueitToken);
        }

        $response = $next($request);
        return $response;

    }
}
