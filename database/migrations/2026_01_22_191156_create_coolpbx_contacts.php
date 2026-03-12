<?php

use App\Models\Contact;
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
        $tableName = Contact::getTableName();
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
        $tableName = Contact::getTableName();
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

        $table->uuid('contact_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('contact_parent_uuid')->nullable(false);
        $table->enum('contact_type', ['customer','contractor','friend','lead','member','family','subscriber','supplier','provider','user','volunteer'])->nullable();
        $table->string('contact_organization', length: 255)->nullable();
        $table->string('contact_name_prefix', length: 255)->nullable();
        $table->string('contact_name_given', length: 255)->nullable();
        $table->string('contact_name_middle', length: 255)->nullable();
        $table->string('contact_name_family', length: 255)->nullable();
        $table->string('contact_name_suffix', length: 255)->nullable();
        $table->string('contact_nickname', length: 255)->nullable();
        $table->string('contact_title', length: 255)->nullable();
        $table->string('contact_role', length: 255)->nullable();
        $table->string('contact_category', length: 255)->nullable();
        $table->string('contact_timezone', length: 255)->nullable();
        $table->longText('contact_note')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX contacts information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
