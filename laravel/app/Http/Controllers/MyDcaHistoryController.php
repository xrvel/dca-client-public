<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MyDcaHistory;
use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;

class MyDcaHistoryController extends Controller
{
	public function get_all()
	{
		$dca_history = MyDcaHistory::orderBy('id', 'desc')->paginate(20);
		return view('inner.dcahistory.index', compact('dca_history'));
	}
}
