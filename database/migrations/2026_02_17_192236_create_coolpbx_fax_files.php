<?php

use App\Models\FaxFile;
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
        $tableName = FaxFile::getTableName();
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
        $tableName = FaxFile::getTableName();
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

        $table->uuid('fax_file_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('fax_uuid')->nullable(false);
        $table->enum('fax_mode', ['rx','tx'])->nullable(false);
        $table->string('fax_destination', length: 255)->nullable(false);
        $table->string('fax_file_type', length: 255)->nullable(false);
        $table->string('fax_file_path', length: 255)->nullable(false);
        $table->string('fax_caller_id_name', length: 255)->nullable(false);
        $table->string('fax_caller_id_number', length: 255)->nullable(false);
        $table->date('insert_date')->nullable()->comment('Deprecated');
        $table->unsignedBigInteger('fax_epoch')->nullable(false);
        $table->longText('fax_base64')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX fax file detail information');
    }
};
