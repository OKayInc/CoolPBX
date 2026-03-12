<?php

use App\Models\Domain;
use App\Models\FaxLog;
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
        $tableName = FaxLog::getTableName();
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
        $tableName = FaxLog::getTableName();
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

        $table->uuid('fax_log_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('fax_uuid')->nullable(false);
        $table->enum('fax_success', ['0','1'])->default('0')->nullable(false)->comment('This will change.');
        $table->unsignedTinyInteger('fax_result_code')->default(0)->nullable(false);
        $table->string('fax_result_text', length: 255)->nullable(false);
        $table->string('fax_file', length: 255)->nullable(false);
        $table->enum('fax_ecm_used', ['on','off','true','false'])->nullable(false);
        $table->string('fax_local_station_id', length: 255)->nullable();
        $table->unsignedTinyInteger('fax_document_transferred_pages')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_document_total_pages')->default(0)->nullable(false);
        $table->string('fax_image_resolution', length: 255)->nullable(false);
        $table->unsignedBigInteger('fax_image_size')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_bad_rows')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_transfer_rate')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_retry_attempts')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_retry_limit')->default(0)->nullable(false);
        $table->unsignedTinyInteger('fax_retry_sleep')->default(0)->nullable(false);
        $table->text('fax_uri')->nullable();
        $table->unsignedTinyInteger('fax_duration')->default(0)->nullable(false);
        $table->date('fax_date')->nullable();
        $table->unsignedBigInteger('fax_epoch')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX fax log detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
