<?php
function my_guess_currency($symbol): string
{
	$symbol = strtolower($symbol);
	// if $symbol ended with "_idr" then return "IDR"
	if (preg_match('/\_idr$/', $symbol)) {
		return 'IDR';
	}

	return 'USD';
}

if (!function_exists('my_json_number_format')) {
	function my_json_number_format($number, $json_data): string
	{
		if (!isset($json_data['decimal'])) {
			$json_data['decimal'] = 2;
		}
		return number_format($number, $json_data['decimal']);
	}
}

if (!function_exists('my_get_final_dca_output')) {
	function my_get_final_dca_output($options): array
	{
		$return = [
			'error' => false,
			'final_amount' => 0,
			'buy_risk' => 0,
			'multiplier' => 0,
			'multiply_if_less_than' => [],
			'multiply_reduce_if_more_than' => [],
		];

		ksort($return);

		if (!isset($options['algorithm'])) {
			$options['algorithm'] = 'log_1';
		}

		if (!isset($options['base_amount'])) {
			$options['base_amount'] = 10;
		}

		if (!isset($options['debug'])) {
			$options['debug'] = false;
		}

		if (!isset($options['risk'])) {
			$options['risk'] = 0;
		}

		if (0 == $options['risk']) {
			$return['error'] = 'Risk is 0';
			return $return;
		}

		if ('log_1' == $options['algorithm']) {
			$multiply_if_less_than = [
				5 => 1,
				10 => 1.01,
				20 => 1.02,
				25 => 1.03,
				30 => 1.04,
				40 => 1.05,
				45 => 1.06,
			];
			$return['multiply_if_less_than'] = $multiply_if_less_than;

			$multiply_reduce_if_more_than = [
				50 => 0.7,
				55 => 0.65,
				60 => 0.6,
				65 => 0.575,
				70 => 0.55,
				75 => 0.5,
				80 => 0.1,
				85 => 0.1,
				90 => 0.1,
				95 => 0.01,
			];
			$return['multiply_reduce_if_more_than'] = $multiply_reduce_if_more_than;
		} else if ('log_2' == $options['algorithm']) {
			$multiply_if_less_than = [
				5 => 4,
				10 => 3.5,
				20 => 3,
				25 => 2.5,
				30 => 1.5,
				40 => 1.25,
				45 => 1.1,
			];
			$return['multiply_if_less_than'] = $multiply_if_less_than;

			$multiply_reduce_if_more_than = [
				50 => 0.7,
				55 => 0.65,
				60 => 0.6,
				65 => 0.575,
				70 => 0.55,
				75 => 0.5,
				80 => 0.45,
				85 => 0.4,
				90 => 0.1,
				95 => 0.01,
			];
			$return['multiply_reduce_if_more_than'] = $multiply_reduce_if_more_than;
		} else if ('log_low_1' == $options['algorithm']) {
			$multiply_if_less_than = [
				5 => 4,
				10 => 3,
				20 => 2,
				25 => 1.5,
				30 => 1.1,
				35 => 1,
			];
			$return['multiply_if_less_than'] = $multiply_if_less_than;

			$multiply_reduce_if_more_than = [
				40 => 0.3,
				45 => 0.3,
				50 => 0.25,
				55 => 0.2,
				60 => 0.15,
				65 => 0.1,
				70 => 0.1,
				75 => 0.1,
				80 => 0.1,
				85 => 0.1,
				90 => 0.1,
				95 => 0.01,
			];
			$return['multiply_reduce_if_more_than'] = $multiply_reduce_if_more_than;
		}

		$multiplier = 1;

		$buy_risk = 100 - $options['risk'];
		$return['buy_risk'] = $buy_risk;

		if (in_array($options['algorithm'], ['log_1', 'log_2', 'log_low_1'])) {
			foreach ($multiply_if_less_than as $_risk => $_multiplier) {
				if ($options['risk'] < $_risk) {
					$multiplier = $_multiplier;
					break;
				}
			}

			foreach ($multiply_reduce_if_more_than as $_risk => $_multiplier) {
				if ($options['risk'] > $_risk) {
					$multiplier = $_multiplier;
				}
			}
		} else if ('square_1' == $options['algorithm']) {
			if ($options['risk'] < 50) {
				$multiplier = ($buy_risk * $buy_risk) / 2500;
			} else if ($options['risk'] < 60) {
				$multiplier = ($buy_risk * $buy_risk) / 3500;
			} else {
				$multiplier = ($buy_risk * $buy_risk) / 4000;
			}
		}

		$return['multiplier'] = $multiplier;

		if ('fixed' == $options['algorithm']) {
			$return['final_amount'] = $options['base_amount'];
		} else {
			$return['final_amount'] = round(($options['base_amount'] * ($buy_risk / 100) * $multiplier), 2);
		}

		return $return;
	}
}
