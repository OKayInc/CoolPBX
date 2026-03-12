<?php

use App\Models\Domain;
use App\Models\User;
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
        $tableName = User::getTableName();
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
        $tableName = User::getTableName();
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

        $table->uuid('voicemail_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('voicemail_id', length: 255)->nullable(false);
        $table->string('voicemail_password', length: 60)->nullable(false);
        $table->unsignedTinyInteger('greeting_id')->default(0)->nullable(false);
        $table->unsignedTinyInteger('voicemail_alternate_greet_id')->nullable();
        $table->string('voicemail_mail_to', length: 255)->nullable();
        $table->string('voicemail_sms_to', length: 255)->nullable();
        $table->enum('voicemail_transcription_enabled', ['true','false'])->default('false')->nullable(false);
        $table->string('voicemail_attach_file', length: 255)->nullable();
        $table->enum('voicemail_file', ['link','attach'])->nullable();
        $table->enum('voicemail_local_after_email', ['true','false'])->default('false')->nullable(false);
        $table->longText('voicemail_name_base64')->nullable();
        $table->enum('voicemail_tutorial', ['true','false'])->default('false')->nullable(false);
        $table->longText('voicemail_description')->nullable();
        $table->enum('voicemail_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX voicemail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
