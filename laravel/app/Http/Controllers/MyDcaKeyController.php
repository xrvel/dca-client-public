<?php

namespace App\Http\Controllers;

use App\Models\MyDcaKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MyExchange;

class MyDcaKeyController extends Controller
{
	public function index()
	{
		$dcakeys = MyDcaKey::where('user_id', Auth::user()->id)->paginate(10);
		return view('inner.dcakeys.index', compact('dcakeys'));
	}

	public function create()
	{
		$exchange = new MyExchange();
		$exchanges = $exchange->get_exchanges();
		return view('inner.dcakeys.create', compact('exchanges'));
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'label' => 'required|string|max:100',
			'exchange_name' => 'required|string|max:100',
			'api_key' => 'required|string|max:200',
			'api_secret' => 'required|string|max:200',
		]);

		$validated['user_id'] = Auth::user()->id;

		$dcakey = MyDcaKey::create($validated);

		return redirect()->route('dcakeys.index', $dcakey->id)
			->with('success', 'Exchange Key created successfully. ID: ' . $dcakey->id);
	}

	public function edit(MyDcaKey $dcakey)
	{
		if ($dcakey->user_id != Auth::user()->id) {
			return redirect()->route('dcakeys.index')
				->with('error', 'You do not have permission to edit this Exchange Key.');
		}

		$exchange = new MyExchange();
		$exchanges = $exchange->get_exchanges();

		return view('inner.dcakeys.edit', compact('dcakey', 'exchanges'));
	}

	public function update(Request $request, MyDcaKey $dcakey)
	{
		if ($dcakey->user_id != Auth::user()->id) {
			return redirect()->route('dcakeys.index')
				->with('error', 'You do not have permission to edit this Exchange Key.');
		}

		$validated = $request->validate([
			'label' => 'required|string|max:100',
			'exchange_name' => 'required|string|max:100',
			'api_key' => 'required|string|max:200',
			'api_secret' => 'required|string|max:200',
		]);

		$dcakey->update($validated);

		return redirect()->route('dcakeys.index', $dcakey->id)
			->with('success', 'DCA Key updated successfully.');
	}

	public function destroy(MyDcaKey $dcakey)
	{
		if ($dcakey->user_id != Auth::user()->id) {
			return redirect()->route('dcakeys.index')
				->with('error', 'You do not have permission to edit this Exchange Key.');
		}

		$dcakey->delete();

		return redirect()->route('dcakeys.index')
			->with('success', 'Exchange Key deleted successfully.');
	}
}
