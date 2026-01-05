<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ConferenceSessionDetail extends Pivot
{
	use HasFactory, HasUniqueIdentifier, GetTableName;
	protected $table = 'v_conference_session_details';
	protected $primaryKey = 'conference_session_detail_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

	protected $fillable = [
        'domain_uuid',
        'conference_session_uuid',
        'conference_session_detail_uuid',
        'meeting_uuid',
		'username',
		'caller_id_name',
		'caller_id_number',
		'uuid',
		'moderator',
		'network_addr',
        'start_epoch',
        'stop_epoch',
	];

	public function session(): BelongsTo {
		return $this->belongsTo(ConferenceSession::class, 'conference_session_uuid', 'conference_session_uuid');
	}
}
