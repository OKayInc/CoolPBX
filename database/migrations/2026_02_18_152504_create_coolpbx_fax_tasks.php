<?php

use App\Models\FaxTask;
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
        $tableName = FaxTask::getTableName();
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
            $tableName = FaxTask::getTableName();
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

            $table->uuid('fax_task_uuid')->nullable(false)->primary()->first();
            $table->uuid('fax_uuid')->nullable(false);
            $table->timestamp('task_next_time', precision: 0)->nullable();
            $table->timestamp('task_lock_time', precision: 0)->nullable();
            $table->string('task_fax_file', length: 255)->nullable();
            $table->string('task_wav_file', length: 255)->nullable();
            $table->string('task_uri', length: 255)->nullable();
            $table->longText('task_dial_string')->nullable();
            $table->string('task_dtmf', length: 255)->nullable();
            $table->string('task_reply_address', length: 255)->nullable();
            $table->enum('task_interrupted', ['true','false'])->default('false')->nullable(false);
            $table->unsignedInteger('task_no_answer_counter')->default(0)->nullable();
            $table->unsignedInteger('task_no_answer_retry_counter')->default(0)->nullable();
            $table->unsignedInteger('task_retry_counter')->default(0)->nullable();
            $table->longText('task_description')->nullable();

            // timestamps
            $table->date('insert_date')->nullable();
            $table->uuid('insert_user')->nullable();
            $table->date('update_date')->nullable();
            $table->uuid('update_user')->nullable();
            $table->comment('CoolPBX FAX task information');
        }
};
