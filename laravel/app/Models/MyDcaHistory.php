<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // my custom
use App\Models\MyDcaKey;
use App\Models\MyDcaSchedule;

class MyDcaHistory extends Model
{
	use HasFactory;

	// Define the table name
	protected $table = 'my_dca_history';

	// Set primary key
	protected $primaryKey = 'id';

	// Disable timestamps management
	public $timestamps = true;

	// Define the attributes that are mass assignable
	protected $fillable = [
		'user_id',
		'schedule_id',
		'key_id',
		'pair_name',
		'buy_strategy',
		'original_amount',
		'adjusted_amount',
		'scheduled_every',
		'scheduled_every_option',
		'risk_source',
		'risk_symbol',
		'max_risk_buy',
		'risk_1',
		'risk_2',
		'risk_3',
		'dca_note',
		'is_error',
		'is_extra_buy',
	];

	public function dca_key(): BelongsTo
	{
		return $this->belongsTo(MyDcaKey::class, 'key_id');
	}

	public function dca_schedule(): BelongsTo
	{
		return $this->belongsTo(MyDcaSchedule::class, 'schedule_id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
