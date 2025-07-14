<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\MyAlphasquared;

class MyRisk
{
	public function get($symbol)
	{
		$return = [
			'error' => false,
			'risk' => 0,
		];

		if (empty($symbol)) {
			$return['error'] = 'Symbol is empty';
			return $return;
		}

		////////////////////////////

		$value = Cache::remember("alphasquared_risk_" . $symbol, 60, function () use ($symbol) {
			$alphasquared = new MyAlphasquared();
			return $alphasquared->get_risk($symbol);
		});

		if ($value['error']) {
			$value['response']['current_risk'] = 0;
		}
		if (!isset($value['response'])) {
			$value['response']['current_risk'] = 0;
		}

		$alpha_risk = $value['response']['current_risk'];

		$return['risk'] = round($alpha_risk, 2);

		return $return;
	}
}
