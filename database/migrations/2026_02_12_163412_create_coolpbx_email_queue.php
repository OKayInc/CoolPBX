<?php

use App\Models\EmailQueue;
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
        $tableName = EmailQueue::getTableName();
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
        $tableName = EmailQueue::getTableName();
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

        $table->uuid('email_queue_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('hostname', length: 255)->nullable(false);
        $table->date('email_date')->nullable();
        $table->string('email_from', length: 255)->nullable(false);
        $table->string('email_to', length: 255)->nullable(false);
        $table->string('email_subject', length: 255)->nullable();
        $table->string('email_body', length: 255)->nullable();
        $table->enum('email_status', ['waiting','trying','sent','failed'])->default('waiting')->nullable(false);
        $table->unsignedTinyInteger('email_retry_count')->default(0)->nullable(false);
        $table->string('email_action_before', length: 255)->nullable();
        $table->string('email_action_after', length: 255)->nullable();
        $table->uuid('email_uuid')->nullable(false);
        $table->longText('email_transcription')->nullable();
        $table->longText('email_response')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX email queue detail information');
    }
};
