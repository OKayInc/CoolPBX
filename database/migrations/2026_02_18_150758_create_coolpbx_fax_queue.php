<?php

use App\Models\Domain;
use App\Models\FaxQueue;
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
        $tableName = FaxQueue::getTableName();
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
            $tableName = FaxQueue::getTableName();
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

            $table->uuid('fax_queue_uuid')->nullable(false)->primary()->first();
            $table->uuid('domain_uuid')->nullable(false);
            $table->uuid('fax_uuid')->nullable(false);
            $table->uuid('origination_uuid')->nullable();
            $table->uuid('fax_log_uuid')->nullable(false);
            $table->date('fax_date')->nullable(false)->comment('This may dissapear.');
            $table->string('hostname', length: 255)->nullable(false);
            $table->string('fax_caller_id_name', length: 255)->nullable();
            $table->string('fax_caller_id_number', length: 255)->nullable();
            $table->string('fax_number', length: 255)->nullable();
            $table->string('fax_prefix', length: 255)->nullable(false);
            $table->string('fax_email_address', length: 255)->nullable();
            $table->string('fax_file', length: 255)->nullable();
            $table->enum('fax_status', ['waiting','trying','sending','sent','busy','failed'])->default('waiting')->nullable(false);
            $table->date('fax_retry_date')->nullable();
            $table->boolean('fax_notify_sent')->default(false)->nullable(false);
            $table->date('fax_notify_date')->nullable();
            $table->unsignedInteger('fax_retry_count')->default(0)->nullable();
            $table->string('fax_accountcode', length: 255)->nullable();
            $table->longText('fax_command')->nullable();

            // timestamps
            $table->date('insert_date')->nullable();
            $table->uuid('insert_user')->nullable();
            $table->date('update_date')->nullable();
            $table->uuid('update_user')->nullable();
            $table->comment('CoolPBX FAX queue information');

            $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
        }
};
