<?php

use App\Models\ContactAttachment;
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
        $tableName = ContactAttachment::getTableName();
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
        $tableName = ContactAttachment::getTableName();
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

        $table->uuid('contact_attachment_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('contact_uuid')->nullable(false);
        $table->unsignedTinyInteger('attachment_primary')->default(0)->nullable(false);
        $table->string('attachment_filename', length: 255)->nullable(false);
        $table->longText('attachment_content')->nullable();
        $table->date('attachment_uploaded_date')->nullable()->comment('deprecated field');
        $table->uuid('attachment_uploaded_user_uuid')->nullable()->comment('deprecated field');
        $table->longText('attachment_description')->nullable();

        $table->enum('address_type', ['work','home','other'])->nullable();
        $table->enum('address_label', ['work','home','other'])->nullable();
        $table->unsignedTinyInteger('address_primary')->default(0)->nullable(false);
        $table->string('address_street', length: 255)->nullable(false);
        $table->string('address_extended', length: 255)->nullable();
        $table->string('address_community', length: 255)->nullable();
        $table->string('address_locality', length: 255)->nullable();
        $table->string('address_region', length: 255)->nullable();
        $table->string('address_postal_code', length: 255)->nullable();
        $table->string('address_country', length: 255)->nullable();
        $table->tinyInteger('address_latitude')->nullable();
        $table->tinyInteger('address_longitude')->nullable();
        $table->longText('address_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX contact attachment detail information');
    }
};
