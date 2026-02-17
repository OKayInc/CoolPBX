<?php

namespace App\Models;

use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory, HasUniqueIdentifier, GetTableName, HandlesStringBooleans;

    protected $table = "v_email_templates";
    protected $primaryKey = "email_template_uuid";
    public $incrementing = false;
    protected $keyType = "string";

    public $timestamps = false;

    protected $fillable = [
        'domain_uuid',
        'template_language',
        'template_category',
        'template_subcategory',
        'template_subject',
        'template_body',
        'template_type',
        'template_enabled',
        'template_description',
    ];

    protected $casts = [
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
