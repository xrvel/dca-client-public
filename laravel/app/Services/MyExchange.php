<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\MyBinance;
use App\Services\MyBinanceProxy;
use App\Services\MyCcxtExchange;
use App\Services\MyIndodax;

class MyExchange
{
	public function buy_market($name, $pair, $amount, $api_key, $api_secret)
	{
		$result = [
			'error' => true,
			'message' => '',
			'data' => [],
		];

		$name = trim(strtolower($name));
		$pair = strtolower($pair);

		// Try CCXT first for supported exchanges
		$ccxt = new MyCcxtExchange();
		if ($ccxt->is_supported($name)) {
			return $ccxt->buy_market($name, $pair, $amount, $api_key, $api_secret);
		}

		// Fallback to custom implementations
		if ('indodax' == $name) {
			$indodax = new MyIndodax();
			$result = $indodax->trade([
				'pair' => $pair,
				'type' => 'buy',
				'order_type' => 'market',
				'idr' => $amount,
			], [
				'api_key' => $api_key,
				'api_secret' => $api_secret,
			]);

			if (isset($result['success']) && 1 == $result['success']) {
				$result['error'] = false;
				$result['message'] = 'OK';
				$result['data'] = $result;
			} else {
				$result['error'] = true;
				$result['message'] = 'Error';
				$result['data'] = $result;
			}
		} else if ('binance_proxy' == $name) {
			$binance_proxy = new MyBinanceProxy();
			$result = $binance_proxy->spot_buy_market($pair, $amount, [
				'api_key' => $api_key,
				'api_secret' => $api_secret,
			]);
		}

		return $result;
	}

	public function check_connection($name, $api_key, $api_secret)
	{
		$name = trim(strtolower($name));

		// Try CCXT first for supported exchanges
		$ccxt = new MyCcxtExchange();
		if ($ccxt->is_supported($name)) {
			return $ccxt->check_connection($name, $api_key, $api_secret);
		}

		// Fallback to custom implementations
		if ('indodax' == $name) {
			$indodax = new MyIndodax();
			$result = $indodax->check_balance(['api_key' => $api_key, 'api_secret' => $api_secret]);
			if (isset($result['success']) && $result['success']) {
				if (isset($result['return']['user_id']) && 0 != $result['return']['user_id'] && isset($result['return']['name'])) {
					return [
						'is_error' => false,
						'message' => 'Name : ' . $result['return']['name'] . '.',
					];
				}
			}
			return ['is_error' => true, 'message' => 'Possibly API key and/or secret are not valid.'];
		} else if ('binance_proxy' == $name) {
			$binance_proxy = new MyBinanceProxy();
			$result = $binance_proxy->check_balance(['api_key' => $api_key, 'api_secret' => $api_secret]);
			if (isset($result['response']) && isset($result['response']['uid'])) {
				return [
					'is_error' => false,
					'message' => 'UID : ' . $result['response']['uid'] . '.',
				];
			}
			return ['is_error' => true, 'message' => 'Possibly API key and/or secret are not valid.'];
		}

		return [
			'is_error' => true,
			'message' => 'Exchange ' . $name . ' is not supported.',
		];
	}

	public function get_exchanges()
	{
		$exchanges = [
			'indodax' => 'Indodax',
			'binance_direct' => 'Binance Direct',
			'binance_proxy' => 'Binance Proxy',
		];

		// Add CCXT supported exchanges
		$ccxt = new MyCcxtExchange();
		$ccxt_exchanges = $ccxt->get_supported_exchanges();

		foreach ($ccxt_exchanges as $exchange) {
			$exchanges[$exchange] = ucfirst($exchange);
		}

		return $exchanges;
	}

	public function is_supported($name): bool
	{
		$name = trim(strtolower($name));

		// Check CCXT supported exchanges first
		$ccxt = new MyCcxtExchange();
		if ($ccxt->is_supported($name)) {
			return true;
		}

		// Check custom implementations
		if ('indodax' == $name) {
			return true;
		}

		if ('binance_proxy' == $name) {
			return true;
		}

		return false;
	}
}
