<?php

use App\Models\Domain;
use App\Models\VoicemailMessage;
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
        $tableName = VoicemailMessage::getTableName();
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
        $tableName = VoicemailMessage::getTableName();
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

        $table->uuid('voicemail_message_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('voicemail_uuid')->nullable(false);
        $table->unsignedBigInteger('created_epoch')->nullable(false);
        $table->unsignedBigInteger('read_epoch')->nullable(false);
        $table->string('caller_id_name', length: 255)->nullable(false);
        $table->string('caller_id_number', length: 255)->nullable(false);
        $table->unsignedBigInteger('message_length')->default(0)->nullable(false);
        $table->enum('message_status', ['saved',''])->default('')->nullable();

        $table->string('voicemail_id', length: 255)->nullable(false)->comment('Deprecated, should be voicemail_uuid.');
        $table->unsignedTinyInteger('greeting_id')->default(0)->nullable(false);
        $table->string('greeting_name', length: 255)->nullable(false);
        $table->string('greeting_filename', length: 255)->nullable();
        $table->longText('greeting_base64')->nullable();
        $table->longText('greeting_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX voicemail greeting information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
