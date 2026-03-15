<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DatabaseTransaction extends Model
{
	use CreatedUpdatedBy, GetTableName, HandlesStringBooleans, HasApiTokens, HasFactory, HasUniqueIdentifier, Notifiable;

	protected $table = 'v_database_transactions';
	protected $primaryKey = 'database_transaction_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $fillable = [
		'domain_uuid',
		'user_uuid',
		'app_name',
		'app_uuid',
		'transaction_code',
		'transaction_address',
		'transaction_type',
		'transaction_date',
		'transaction_old',
		'transaction_new',
		'transaction_result',
	];
}
