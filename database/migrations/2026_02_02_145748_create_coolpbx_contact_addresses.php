<?php

use App\Models\ContactAddress;
use App\Models\Domain;
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
        $tableName = ContactAddress::getTableName();
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
        $tableName = ContactAddress::getTableName();
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

        $table->uuid('contact_address_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('contact_uuid')->nullable(false);
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
        $table->comment('CoolPBX contact address detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
