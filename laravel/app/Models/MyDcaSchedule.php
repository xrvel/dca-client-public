<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // my custom

class MyDcaSchedule extends Model
{
	use HasFactory;

	// Define the table name
	protected $table = 'my_dca_schedules';

	// Set primary key
	protected $primaryKey = 'id';

	// Disable timestamps management
	public $timestamps = true;

	// Define the attributes that are mass assignable
	protected $fillable = [
		'user_id',
		'label',
		'pair_name',
		'key_id',
		'buy_strategy',
		'base_amount',
		'scheduled_every',
		'scheduled_every_option',
		'risk_source',
		'risk_symbol',
		'min_risk_buy',
		'max_risk_buy',
		'min_buy_amount',
		'max_buy_amount',
		'description',
		'schedule_last_run',
		'is_active',
		'enable_extra_buys',
		'extra_buys_reset_interval',
		'extra_buys_executed_count',
		'max_extra_buys_per_interval',
		'min_hours_between_extra_buys',
		'reset_mode',
		'last_extra_buy_timestamp',
	];

	public function dca_key(): BelongsTo
	{
		return $this->belongsTo(MyDcaKey::class, 'key_id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
