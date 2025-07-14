<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyIndodax
{
	public function check_balance($keys = [])
	{
		return $this->btcid_query('getInfo', [], $keys);
	}

	// Trade
	public function trade($options = [], $keys = [])
	{
		$result = $this->btcid_query('trade', $options, $keys);

		return $result;
	}

	public function btcid_query($method = 'trade', array $req = [], $keys = [])
	{
		if (!is_array($keys)) {
			return [
				'error' => 'API key and/or secret are not within an array',
			];
		}

		$key = $keys['api_key'];
		$secret = $keys['api_secret'];

		if ('' == $key || '' == $secret) {
			return [
				'error' => 'API key and/or secret are not set',
			];
		}

		//var_dump($key, $secret);

		$req['method'] = $method;
		$req['nonce'] = time();

		// generate the POST data string
		$post_data = http_build_query($req, '', '&');
		$sign = hash_hmac('sha512', $post_data, $secret);

		////////////////////////////

		$userAgent = 'Mozilla/4.0 (compatible; BITCOINCOID PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')';
		$url = 'https://indodax.com/tapi/';

		$response = Http::asForm()
			->withHeaders([
				'Sign' => $sign,
				'Key' => $key,
				'User-Agent' => $userAgent,
			])->withOptions([
				'verify' => false,
			])->post($url, $req);

		if ($response->successful()) {
			$_temp = $response->json();
		} else {
			$_temp = [
				'error' => 'HTTP error: ' . $response->status(),
				'error_response' => $response->body(),
			];
		}

		if (is_array($_temp)) {
			ksort($_temp);
		}

		return $_temp;
	}
}
