<?php

use App\Models\Domain;
use App\Models\Extension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = Extension::getTableName();
        if (!Schema::hasTable($tableName))
        {
            Schema::create($tableName, function (Blueprint $table)
            {
                $this->dql($table);
            });
        }
        else
        {
            Schema::table($tableName, function (Blueprint $table) {
                $this->dql($table);
            });
        }    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Extension::getTableName();
        Schema::dropIfExists($tableName);
    }

    private function dql(Blueprint $table): void
    {
        switch (env('DB_CONNECTION', 'mysql'))
        {
            case 'mariadb':
            case 'mysql':
                $table->collation('unicode_ci');
                $table->engine('InnoDB');
                break;
            case 'pgsql':
                $table->collation('en_US.utf8');
        }

        $table->uuid('extension_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('extension', length: 255)->nullable(false);
        $table->string('number_alias', length: 255)->nullable();
        $table->string('password', length: 255)->nullable();
        $table->string('accountcode', length: 255)->nullable();
        $table->string('effective_caller_id_name', length: 255)->nullable();
        $table->string('effective_caller_id_number', length: 255)->nullable();
        $table->string('outbound_caller_id_name', length: 255)->nullable();
        $table->string('outbound_caller_id_number', length: 255)->nullable();
        $table->string('emergency_caller_id_name', length: 255)->nullable();
        $table->string('emergency_caller_id_number', length: 255)->nullable();
        $table->string('directory_first_name', length: 255)->nullable();
        $table->string('directory_last_name', length: 255)->nullable();
        $table->enum('directory_visible', ['true','false'])->default('false')->nullable(false);
        $table->integer('max_registrations')->unsigned()->nullable();
        $table->integer('limit_max')->unsigned()->nullable();
        $table->string('limit_destination', length: 255)->nullable();
        $table->string('missed_call_app', length: 255)->nullable();
        $table->string('missed_call_data', length: 255)->nullable();
        $table->string('user_context', length: 255)->nullable(false);
        $table->longText('toll_allow')->nullable();
        $table->integer('call_timeout')->unsigned()->nullable();
        $table->string('call_group', length: 255)->nullable();
        $table->enum('call_screen_enabled', ['true','false'])->default('false')->nullable(false);
        $table->enum('user_record', ['local','all','inbound','outbound'])->nullable();
        $table->string('hold_music', length: 255)->nullable();
        $table->string('auth_acl', length: 255)->nullable();
        $table->string('cidr', length: 255)->nullable();
        $table->enum('sip_force_contact', ['NDLB-connectile-dysfunction','NDLB-connectile-dysfunction-2.0','NDLB-tls-connectile-dysfunction'])->nullable();
        $table->string('nibble_account', length: 255)->nullable();
        $table->integer('sip_force_expires')->unsigned()->default(0)->nullable();
        $table->string('mwi_account', length: 255)->nullable();
        $table->enum('sip_bypass_media', ['bypass-media','bypass-media-after-bridge','proxy-media'])->nullable();
        $table->longText('dial_string')->nullable();
        $table->string('dial_user', length: 255)->nullable();
        $table->string('dial_domain', length: 255)->nullable();
        $table->enum('do_not_disturb', ['true','false'])->default('false');
        $table->string('forward_all_destination', length: 255)->nullable();
        $table->enum('forward_all_enabled', ['true','false'])->default('false');
        $table->string('forward_busy_destination', length: 255)->nullable();
        $table->enum('forward_busy_enabled', ['true','false'])->default('false');
        $table->string('forward_no_answer_destination', length: 255)->nullable();
        $table->enum('forward_no_answer_enabled', ['true','false'])->default('false');
        $table->string('forward_user_not_registered_destination', length: 255)->nullable();
        $table->enum('forward_user_not_registered_enabled', ['true','false'])->default('false');
        $table->uuid('follow_me_uuid')->nullable();
        $table->enum('follow_me_enabled', ['true','false'])->default('false');
        $table->longText('follow_me_destinations')->nullable();
        $table->enum('enabled', ['true','false'])->default('false');
        $table->longText('description')->nullable();
        $table->longText('absolute_codec_string')->nullable();
        $table->enum('force_ping', ['true','false'])->default('false');
        $table->longText('extra_data')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX SIP extension loging information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
