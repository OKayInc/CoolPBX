<?php

namespace App\Models;

use App\Traits\GetTableName;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class VoicemailDestination extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasUniqueIdentifier, GetTableName;
    protected $table = 'v_voicemail_destinations';
    protected $primaryKey = 'voicemail_destination_uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

    protected $fillable = [
        'voicemail_uuid',
        'voicemail_destination_uuid',
        'domain_uuid',
        'voicemail_uuid_copy',
    ];

    public function voicemail()
    {
        return $this->belongsTo(Voicemail::class, 'voicemail_uuid', 'voicemail_uuid');
    }

    public function destinationVoicemail()
    {
        return $this->belongsTo(Voicemail::class, 'voicemail_uuid_copy', 'voicemail_uuid');
    }
}