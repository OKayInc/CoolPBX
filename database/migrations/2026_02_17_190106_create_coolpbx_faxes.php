<?php

use App\Models\Fax;
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
        $tableName = Fax::getTableName();
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
            $tableName = Fax::getTableName();
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

            $table->uuid('fax_uuid')->nullable(false)->primary()->first();
            $table->uuid('domain_uuid')->nullable(false);
            $table->uuid('dialplan_uuid')->nullable(false);
            $table->string('fax_extension', length: 255)->nullable(false);
            $table->string('accountcode', length: 255)->nullable();
            $table->string('fax_destination_number', length: 255)->nullable();
            $table->string('fax_prefix', length: 255)->nullable();
            $table->string('fax_name', length: 255)->nullable(false);
            $table->string('fax_email', length: 255)->nullable();
            $table->string('fax_email_connection_type', length: 255)->nullable();
            $table->enum('fax_email_connection_type', ['imap','pop3'])->default('imap')->nullable(false);
            $table->string('fax_email_connection_host', length: 255)->nullable();
            $table->unsignedInteger('fax_email_connection_port')->nullable();
            $table->enum('fax_email_connection_security', ['','ssl','tls'])->default('')->nullable(false);
            $table->enum('fax_email_connection_validate', ['true','false'])->default('false')->nullable(false);
            $table->string('fax_email_connection_username', length: 255)->nullable();
            $table->string('fax_email_connection_password', length: 255)->nullable();
            $table->string('fax_email_connection_mailbox', length: 255)->nullable();
            $table->string('fax_email_inbound_subject_tag', length: 255)->nullable();
            $table->string('fax_email_outbound_subject_tag', length: 255)->nullable();
            $table->string('fax_email_outbound_authorized_senders', length: 255)->nullable();
            $table->string('fax_pin_number', length: 255)->nullable();
            $table->string('fax_caller_id_name', length: 255)->nullable();
            $table->string('fax_caller_id_number', length: 255)->nullable();
            $table->string('fax_toll_allow', length: 255)->nullable(false);
            $table->string('fax_forward_number', length: 255)->nullable();
            $table->string('fax_send_greeting', length: 255)->nullable();
            $table->integer('fax_send_channels')->unsigned()->default(10)->nullable();
            $table->longText('fax_description')->nullable();

            // timestamps
            $table->date('insert_date')->nullable();
            $table->uuid('insert_user')->nullable();
            $table->date('update_date')->nullable();
            $table->uuid('update_user')->nullable();
            $table->comment('CoolPBX FAX extension information');
        }
};
