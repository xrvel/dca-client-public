<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MyAlphasquared
{
	public function get_risk($symbol = 'BTC')
	{
		$return = [
			'error' => false,
			'response' => [],
		];
		ksort($return);

		if ('' == env('ALPHASQUARED_API_KEY')) {
			$return['error'] = 'Some setting is not set (' . __LINE__ . ')';
			return $return;
		}

		$symbol = trim(strtoupper($symbol));

		if (empty($symbol)) {
			$return['error'] = 'Symbol is empty.';
			return $return;
		}

		$url = 'https://alphasquared.io/wp-json/as/v1/asset-info?symbol=' . $symbol;
		$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';

		$response = Http::withHeaders([
			'User-Agent' => $userAgent,
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Authorization' => env('ALPHASQUARED_API_KEY'),
		])->withOptions([
			'verify' => false,
		])->get($url);

		if ($response->successful()) {
			$return['response'] = $response->json();
		} else {
			$return['error'] = 'API error: ' . $response->status();
			return $return;
		}

		return $return;
	}
}
