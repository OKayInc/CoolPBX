<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactAddress;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\ContactUrl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContactSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Schema::hasColumn(ContactPhone::getTableName(), 'phone_type')) {
            $sql = "UPDATE ".ContactPhone::getTableName()." SET phone_type_voice = 1 WHERE phone_type IN ('home','work','voice','voicemail','cell','pcs');";
            DB:statement($sql);
            unset($sql);

            $sql = "UPDATE ".ContactPhone::getTableName()." SET phone_type_fax = 1 WHERE phone_type = 'fax';";
            DB:statement($sql);
            unset($sql);

            $sql = "UPDATE ".ContactPhone::getTableName()." SET phone_type_video = 1 WHERE phone_type = 'video';";
            DB:statement($sql);
            unset($sql);

            $sql = "UPDATE ".ContactPhone::getTableName()." SET phone_type_text = 1 WHERE phone_type IN ('cell', 'pager');";
            DB:statement($sql);
            unset($sql);
        }

        if (Schema::hasColumn(Contact::getTableName(), 'contact_email')) {
            foreach (Contact::all() as $m)
            {
                if (!empty($m->contact_email)){
                    // Find if ContactEmail has it
                    $c = ContactEmail::where('contact_uuid', '=', Contact::getTableName().'.contact_uuid')
                        ->where('email_address','=', $m->contact_email)
                        ->count();
                    if ($c == 0){
                        // Add it, it doesn't exist
                        $data['domain_uuid'] = $m->domain_uuid;
                        $data['contact_uuid'] = $m->contact_uuid;
                        $data['email_primary'] = 1;
                        $data['email_address'] = $m->contact_email;
                        ContactEmail::create($data);
                        unset($data);
                    }
                }
            }
        }

        if (Schema::hasColumn(Contact::getTableName(), 'contact_url')) {
            foreach (Contact::all() as $m)
            {
                if (!empty($m->contact_url)){
                    // Find if ContactEmail has it
                    $c = ContactEmail::where('contact_uuid', '=', Contact::getTableName().'.contact_uuid')
                    ->where('contact_url','=', $m->contact_url)
                    ->count();
                    if ($c == 0){
                        // Add it, it doesn't exist
                        $data['domain_uuid'] = $m->domain_uuid;
                        $data['contact_uuid'] = $m->contact_uuid;
                        $data['url_primary'] = 1;
                        $data['url_address'] = $m->contact_url;
                        ContactEmail::create($data);
                        unset($data);
                    }
                }
            }
        }

        $sql = "UPDATE ".ContactAddress::getTableName()." SET address_primary = 0 WHERE address_primary IS NULL;";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".ContactEmail::getTableName()." SET email_primary = 0 WHERE email_primary IS NULL;";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".ContactPhone::getTableName()." SET phone_primary = 0 WHERE phone_primary IS NULL;";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".ContactUrl::getTableName()." SET url_primary = 0 WHERE url_primary IS NULL;";
        DB:statement($sql);
        unset($sql);
    }
}
