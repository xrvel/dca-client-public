<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyBinanceProxy
{
	public function check_balance($keys = [])
	{
		return $this->raw_request([
			'mode' => 'check_account',
		], $keys);
	}

	public function spot_buy_market($symbol = 'BTCUSDT', $usdt_quantity = 10, $keys = [])
	{
		return $this->raw_request([
			'mode' => 'spot_buy_market',
			'symbol' => $symbol,
			'quoteOrderQty' => $usdt_quantity
		], $keys);
	}

	public function raw_request($options = [], $keys = [])
	{
		if (!is_array($keys)) {
			return [
				'error' => true,
				'message' => 'API key and/or secret are not within an array',
				'data' => [],
			];
		}

		$key = $keys['api_key'];
		$secret = $keys['api_secret'];

		if ('' == $key || '' == $secret) {
			return [
				'error' => true,
				'message' => 'API key and/or secret are not set',
				'data' => [],
			];
		}

		$return = [
			'error' => false,
			'http_code' => 0,
			'response' => [],
		];
		ksort($return);

		if (!isset($options['method'])) {
			$options['method'] = 'get';
		}

		$now = time();

		/////////////

		$curl_secret = hash('sha256', $key . $secret);

		$apiSecret = $secret;

		$ts = (microtime(true) * 1000) + 0;

		$curlUrl = 'https://api.binance.com/';

		if ('check_account' == $options['mode']) {
			$params = [
				'recvWindow' => 15000,
				'timestamp' => number_format($ts, 0, '.', ''),
			];

			$query = http_build_query($params);
			$signature = hash_hmac('sha256', $query, $apiSecret);
			$params['signature'] = $signature;

			$query = http_build_query($params);

			$curlUrl .= 'api/v3/account?' . $query;
			$options['method'] = 'get';
		} elseif ('spot_buy_market' == $options['mode']) {
			$params = [
				'symbol' => strtoupper($options['symbol']),
				'side' => 'BUY',
				'type' => 'MARKET',
				'quoteOrderQty' => $options['quoteOrderQty'],
				'recvWindow' => 15000,
				'timestamp' => number_format($ts, 0, '.', ''),
			];

			$query = http_build_query($params);
			$signature = hash_hmac('sha256', $query, $apiSecret);
			$query .= "&signature=" . $signature;

			$curlUrl .= 'api/v3/order?' . $query;

			$options['method'] = 'post';
		} else {
			return $return;
		}

		// POST data to curl proxy
		$curl_post_data = [
			'method' => $options['method'],
			'url' => base64_encode($curlUrl),
			'timestamp' => $now,
		];

		// Curl proxy URL
		$url = env('DCA_BINANCE_PROXY_URL');
		$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';

		// Do the request to curl proxy
		$response = Http::asForm()
			->withHeaders([
				'X-Smth-Hash' => hash_hmac('sha256', $options['method'] . base64_encode($curlUrl) . $now, $curl_secret),
				'X-Time-Hash' => $key,
				'User-Agent' => $userAgent,
			])->withOptions([
				'verify' => false,
			])->post($url, $curl_post_data);

		$_temp = $response->json();

		$return['response'] = $_temp['response'] ?? [];
		$return['http_code'] = $_temp['http_code'] ?? 0;

		if ('' != $return['response']) {
			$return['response'] = json_decode($return['response'], true);
		}

		if (!$response->successful()) {
			$return['error'] = $_temp['error'];
			return $return;
		}

		return $return;
	}
}
