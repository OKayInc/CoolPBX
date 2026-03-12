<?php

use App\Models\Domain;
use App\Models\XmlCDR;
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
        $tableName = XmlCDR::getTableName();
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = XmlCDR::getTableName();
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

        $table->uuid('xml_cdr_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable();
        $table->uuid('extension_uuid')->nullable();
        $table->uuid('bridge_uuid')->nullable();
        $table->uuid('originating_leg_uuid')->nullable();
        $table->string('sip_call_id', length: 255)->nullable(false);
        $table->string('domain_name', length: 60)->nullable()->comment('Deprecated.');
        $table->string('accountcode', length: 255)->nullable(false);
        $table->enum('direction', ['inbound','local','outbound'])->nullable();
        $table->string('default_language', length: 255)->nullable();
        $table->string('context', length: 255)->nullable();
        $table->string('caller_id_name', length: 255)->nullable();
        $table->string('caller_id_number', length: 255)->nullable();
        $table->string('caller_destination', length: 255)->nullable();
        $table->string('source_number', length: 255)->nullable(false);
        $table->string('destination_number', length: 255)->nullable();
        $table->unsignedBigInteger('start_epoch')->default(0)->nullable(false);
        $table->timestamp('start_stamp', precision: 0)->nullable(false);
        $table->unsignedBigInteger('answer_stamp')->nullable();
        $table->timestamp('answer_epoch', precision: 0)->nullable();
        $table->unsignedBigInteger('end_epoch')->nullable();
        $table->timestamp('end_stamp', precision: 0)->nullable();
        $table->unsignedBigInteger('duration')->default(0)->nullable(false);
        $table->unsignedBigInteger('mduration')->default(0)->nullable(false);
        $table->unsignedBigInteger('billsec')->default(0)->nullable(false);
        $table->unsignedBigInteger('billmsec')->default(0)->nullable(false);
        $table->string('read_codec', length: 255)->nullable();
        $table->enum('read_rate', [8000, 16000, 32000, 48000])->nullable();
        $table->string('write_codec', length: 255)->nullable();
        $table->enum('write_rate', [8000, 16000, 32000, 48000])->nullable();
        $table->ipAddress('remote_media_ip')->nullable();
        $table->ipAddress('network_addr')->nullable();
        $table->string('record_path', length: 255)->nullable();
        $table->string('record_name', length: 255)->nullable();
        $table->unsignedBigInteger('record_length')->nullable();
        $table->enum('leg', ['a', 'b'])->default('a')->nullable();
        $table->unsignedBigInteger('pdd_ms')->nullable();
        $table->unsignedTinyInteger('rtp_audio_in_mos')->nullable();
        $table->string('last_app', length: 255)->nullable();
        $table->string('last_arg', length: 255)->nullable();
        $table->enum('voicemail_message', ['true','false'])->default('false')->nullable(false);
        $table->enum('missed_call', ['true','false'])->default('false')->nullable(false);
        $table->unsignedBigInteger('waitsec')->nullable();
        $table->enum('hangup_cause', ['ALLOTTED_TIMEOUT','ATTENDED_TRANSFER','BLIND_TRANSFER','CALL_REJECTED','CHAN_NOT_IMPLEMENTED','DESTINATION_OUT_OF_ORDER','EXCHANGE_ROUTING_ERROR','INCOMPATIBLE_DESTINATION','INVALID_NUMBER_FORMAT','LOSE_RACE','MANAGER_REQUEST','MANDATORY_IE_MISSING','MEDIA_TIMEOUT','NETWORK_OUT_OF_ORDER','NONE','NORMAL_CLEARING','NORMAL_TEMPORARY_FAILURE','NORMAL_UNSPECIFIED','NO_ANSWER','NO_ROUTE_DESTINATION','NO_USER_RESPONSE','ORIGINATOR_CANCEL','PICKED_OFF','RECOVERY_ON_TIMER_EXPIRE','REQUESTED_CHAN_UNAVAIL','SUBSCRIBER_ABSENT','SYSTEM_SHUTDOWN','UNALLOCATED_NUMBER','USER_BUSY','USER_NOT_REGISTERED'])->nullable(false);
        $table->unsignedTinyInteger('hangup_cause_q850')->nullable();
        $table->enum('sip_hangup_disposition', ['recv_bye','recv_cancel','recv_refuse','send_bye','send_cancel','send_refuse'])->nullable(false);
        $table->string('tags', length: 1024)->nullable();

        # Callcenter
        $table->uuid('call_center_queue_uuid')->nullable();
        $table->enum('cc_side', ['agent','member'])->nullable();
        $table->uuid('cc_member_uuid')->nullable();
        $table->unsignedBigInteger('cc_queue_joined_epoch')->nullable();
        $table->unsignedBigInteger('cc_queue_answered_epoch')->nullable();
        $table->unsignedBigInteger('cc_queue_terminated_epoch')->nullable();
        $table->unsignedBigInteger('cc_queue_canceled_epoch')->nullable();
        $table->string('cc_queue', length: 255)->nullable(false);
        $table->uuid('cc_member_session_uuid')->nullable();
        $table->uuid('cc_agent_uuid')->nullable();
        $table->uuid('cc_agent')->nullable()->comment('It is an uuid because CoolPBX only gives UUIDs to agents.');
        $table->enum('cc_agent_type', ['callback','uuid-standby'])->nullable();
        $table->enum('cc_agent_bridged', ['true','false'])->nullable();
        $table->enum('cc_cancel_reason', ['NONE','TIMEOUT','NO_AGENT_TIMEOUT','BREAK_OUT','EXIT_WITH_KEY'])->nullable();
        $table->enum('cc_cause', ['answered','cancel'])->nullable();

        # Conference
        $table->uuid('conference_uuid')->nullable();
        $table->string('conference_name', length: 255)->nullable();
        $table->text('conference_member_id')->nullable();
        $table->text('digits_dialed')->nullable();
        $table->text('pin_number')->nullable();

        $table->longText('xml')->nullable();
        $table->json('json')->nullable();
        $table->enum('record_type', ['call','audio/wav','audio/mpeg','audio/ogg','text','fax','voicemail'])->nullable();

        # Billing
        $table->decimal('call_buy', total: 10, places: 6)->nullable();
        $table->decimal('call_sell', total: 10, places: 6)->nullable();
        $table->decimal('call_sell_local_currency', total: 10, places: 6)->nullable();
        $table->string('local_currency', length: 3)->nullable();
        $table->string('carrier_name', length: 3)->nullable()->comment('Deprecated.');
        $table->uuid('carrier_uuid')->nullable();
        $table->unsignedTinyInteger('billing_status')->default(0)->nullable(false);
        $table->json('billing_json')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX CDR information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
    }
};
