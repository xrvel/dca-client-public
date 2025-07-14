<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // my custom
use Illuminate\Database\Eloquent\Relations\HasMany; // my custom
use Illuminate\Support\Facades\Crypt;

class MyDcaKey extends Model
{
	use HasFactory;

	// Define the table name
	protected $table = 'my_dca_keys';

	// Set primary key
	protected $primaryKey = 'id';

	// Disable timestamps management
	public $timestamps = true;

	// Define the attributes that are mass assignable
	protected $fillable = [
		'user_id',
		'label',
		'exchange_name',
		'api_key',
		'api_secret',
		'ok_last_check_timestamp',
		'error_last_check_timestamp',
	];

	// Automatically encrypt api_key when setting
	public function setApiKeyAttribute($value)
	{
		$this->attributes['api_key'] = Crypt::encryptString($value);
	}

	// Automatically decrypt api_key when accessing
	public function getApiKeyAttribute($value)
	{
		return Crypt::decryptString($value);
	}

	// Same for api_secret
	public function setApiSecretAttribute($value)
	{
		$this->attributes['api_secret'] = Crypt::encryptString($value);
	}

	public function getApiSecretAttribute($value)
	{
		return Crypt::decryptString($value);
	}

	public function dca_schedules(): HasMany
	{
		return $this->hasMany(MyDcaSchedule::class, 'key_id', 'id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
