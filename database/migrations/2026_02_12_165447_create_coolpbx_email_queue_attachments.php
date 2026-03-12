<?php

use App\Models\Domain;
use App\Models\EmailQueueAttachment;
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
        $tableName = EmailQueueAttachment::getTableName();
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
        $tableName = EmailQueueAttachment::getTableName();
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

        $table->uuid('email_queue_attachment_uuid')->nullable(false)->primary()->first();
        $table->uuid('email_queue_uuid')->nullable(false);
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('email_attachment_mime_type', length: 255)->nullable(false);
        $table->string('email_attachment_type', length: 255)->nullable(false);
        $table->string('email_attachment_path', length: 255)->nullable(false);
        $table->string('email_attachment_name', length: 255)->nullable(false);
        $table->longText('email_attachment_base64')->nullable();
        $table->string('email_attachment_cid', length: 255)->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX email queue attachment detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
