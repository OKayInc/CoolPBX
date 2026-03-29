<?php

use App\Models\ClusterNode;
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
        $tableName = ClusterNode::getTableName();
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
        $tableName = ClusterNode::getTableName();
        Schema::dropIfExists($tableName);
    }

    private function dql(Blueprint $table): void
    {
        switch (env('DB_CONNECTION', 'mysql'))
        {
            case 'mariadb':
            case 'mysql':
                $table->engine('InnoDB');
                break;
            case 'pgsql':
                $table->collation('en_US.utf8');
        }

        $table->uuid('cluster_node_uuid')->nullable(false)->primary();
        $table->string('node_name', length: 100)->nullable(false);
        $table->string('node_hostname', length: 255)->nullable(false);
        $table->integer('xml_rpc_port')->default(8080)->nullable(false);
        $table->enum('node_role', ['pbx', 'database', 'both'])->default('pbx')->nullable(false);
        $table->enum('node_enabled', ['true', 'false'])->default('true')->nullable(false);
        $table->integer('node_priority')->default(1)->nullable(false);
        $table->text('node_description')->nullable();
        
        $table->timestamp('insert_date')->nullable();
        $table->timestamp('update_date')->nullable();
        
        $table->comment('CoolPBX cluster nodes configuration');
        
        $table->index('node_enabled');
        $table->index(['node_role', 'node_enabled']);
    }
};