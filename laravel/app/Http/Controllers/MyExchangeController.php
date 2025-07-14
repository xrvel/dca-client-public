<?php

namespace App\Http\Controllers;

use App\Models\MyDcaKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MyExchange;

class MyExchangeController extends Controller
{
	public function check_api(Request $request)
	{
		$id = intval($request->post('key_id'));

		if (0 == $id) {
			return response('Key ID is not valid', 400)
				->header('Content-Type', 'text/html');
		}

		$dca_key = MyDcaKey::find($id);

		if (!$dca_key) {
			return response('Key ID is not found', 404)
				->header('Content-Type', 'text/html');
		}

		$exchange = new MyExchange();

		$check = $exchange->is_supported($dca_key->exchange_name);

		if (!$check) {
			return response('Exchange is not supported', 400)
				->header('Content-Type', 'text/html');
		}

		$result = $exchange->check_connection($dca_key->exchange_name, $dca_key->api_key, $dca_key->api_secret);

		if ($result['is_error']) {
			MyDcaKey::where('id', $id)->update(['error_last_check_timestamp' => time()]);
			return response('Error : ' . $result['message'], 400)
				->header('Content-Type', 'text/html');
		} else {
			MyDcaKey::where('id', $id)->update(['ok_last_check_timestamp' => time()]);
			return response('Success : ' . $result['message'], 200)
				->header('Content-Type', 'text/html');
		}
	}
}
