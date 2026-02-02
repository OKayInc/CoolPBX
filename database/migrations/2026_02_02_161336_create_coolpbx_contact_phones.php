<?php

use App\Models\ContactNote;
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
        $tableName = ContactNote::getTableName();
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
        $tableName = ContactNote::getTableName();
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

        $table->uuid('contact_phone_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('contact_uuid')->nullable(false);
        $table->enum('email_label', ['work','home','mobile','main','billing','fax','voicemail','text','other'])->nullable();
        $table->unsignedTinyInteger('phone_type_voice')->default(0)->nullable(false);
        $table->unsignedTinyInteger('phone_type_fax')->default(0)->nullable(false);
        $table->unsignedTinyInteger('phone_type_video')->default(0)->nullable(false);
        $table->unsignedTinyInteger('phone_type_text')->default(0)->nullable(false);
        $table->string('phone_speed_dial', length: 255)->nullable();
        $table->string('phone_country_code', length: 3)->nullable();
        $table->string('phone_number', length: 20)->nullable(false);
        $table->string('phone_extension', length: 20)->nullable();
        $table->unsignedTinyInteger('phone_primary')->default(0)->nullable(false);
        $table->longText('phone_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX contact phone detail information');
    }
};
