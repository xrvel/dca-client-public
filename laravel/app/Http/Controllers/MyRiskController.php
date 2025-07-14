<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\MyRisk;

class MyRiskController extends Controller
{
	public function risk_get_api(Request $request)
	{
		$symbol = $request->post('symbol', 'BTC');

		$risk = new MyRisk();
		$return = $risk->get($symbol);

		if ($return['error']) {
			return response($return['error'], 400)
				->header('Content-Type', 'text/html');
			return;
		}

		return response(e($return['risk']), 200)
			->header('Content-Type', 'text/html');
	}

	public function risk_simulation(Request $request)
	{
		return view('inner.risk.simulation');
	}

	public function risk_simulation_api(Request $request)
	{
		$amount = intval($request->post('amount', 0));
		$algorithm = trim($request->post('algorithm', 'linear'));

		$s = '<ul>';
		for ($i = 1; $i <= 99; $i++) {
			$dca_output = my_get_final_dca_output([
				'risk' => $i,
				'base_amount' => $amount,
				'debug' => false,
				'algorithm' => $algorithm,
			]);

			if ($dca_output['error']) {
				$s .= 'Error: ' . $dca_output['error'];
				continue;
			}

			$s .= '<li>Risk = ' . $i . ', Final amount: $ ' . number_format($amount) . ' x ' . $dca_output['buy_risk'] . '% x ' . $dca_output['multiplier'] . ' = $ ' . number_format($dca_output['final_amount'], 2) . '</li>';;
		}
		$s .= '</ul>';

		return response($s, 200)
			->header('Content-Type', 'text/html');
	}
}
