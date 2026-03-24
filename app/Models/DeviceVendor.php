<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
//use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceVendor extends Model
{
	use CreatedUpdatedBy, GetTableName, HandlesStringBooleans, HasApiTokens, HasFactory, Notifiable;
    // HasUniqueIdentifier,

	protected $primaryKey = 'device_vendor_uuid';
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
        'device_vendor_uuid',
        'name',
        'enabled',
        'description',
    ];

	 public function functions(): HasMany {
        return $this->HasMany(DeviceVendorFunction::class, 'device_vendor_uuid', 'device_vendor_uuid');
    }
}
