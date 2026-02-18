<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;


class MenuLanguage extends Pivot
{
	use HasFactory, HasUniqueIdentifier, GetTableName;
	protected $table = 'v_menu_languages';
	protected $primaryKey = 'menu_language_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

	public $fillable = [
		'menu_uuid',
		'menu_item_uuid',
		'menu_language',
		'menu_item_title',
	];
}
