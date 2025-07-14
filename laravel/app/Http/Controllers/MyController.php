<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MyDcaHistory;
use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class MyController extends Controller
{
	public function binance_proxy($id = null)
	{
		$dca_key = MyDcaKey::where('user_id', Auth::user()->id)->where('id', $id)->get();

		if ($dca_key->isEmpty()) {
			redirect()->route('home')->with('error', 'Key not found.');
			return;
		}

		$dca_key = $dca_key->first();

		if ($dca_key->exchange_name != 'binance_proxy') {
			redirect()->route('home')->with('error', 'Valid only for binance_proxy.');
			return;
		}

		//print_r($dca_key->toArray());
		//return;

		$path = storage_path('app/private/binance_proxy.txt');

		if (!file_exists($path)) {
			abort(404, 'File not found.');
		}

		$f = file_get_contents($path);

		$f = str_replace('SOME_SUPER_SECRET_123', hash('sha256', $dca_key->api_key . $dca_key->api_secret), $f);

		return Response::make($f, 200, [
			'Content-Type' => 'text/plain',
		]);
	}


	public function home(Request $request)
	{
		$dca_api_key_ok = (strlen(env('ALPHASQUARED_API_KEY')) > 5);
		$dca_history_count = $dca_key_count = $dca_schedule_count = 0;

		if (Auth::check()) {
			$dca_history_count = MyDcaHistory::where('user_id', Auth::user()->id)
				->count();

			// find MyDcaHistory where user_id = Auth::user()->id
			$dca_key_count = MyDcaKey::where('user_id', Auth::user()->id)
				->count();

			$dca_schedule_count = MyDcaSchedule::where('user_id', Auth::user()->id)
				->where('is_active', 1)
				->count();
		}

		return view('inner.home', compact('dca_api_key_ok', 'dca_key_count', 'dca_schedule_count', 'dca_history_count'));
	}
}
