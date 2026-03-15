<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PinNumber extends Model
{
	use CreatedUpdatedBy, GetTableName, HandlesStringBooleans, HasApiTokens, HasFactory, HasUniqueIdentifier, Notifiable;

	protected $table = 'v_pin_numbers';
	protected $primaryKey = 'pin_number_uuid';
	public $incrementing = false;
	protected $keyType = 'string';
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $fillable = [
		'domain_uuid',
		'pin_number',
		'accountcode',
		'enabled',
        'description',
	];

    public function domain(): BelongsTo {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
