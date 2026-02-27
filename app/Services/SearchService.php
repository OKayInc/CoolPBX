<?php
namespace App\Services;

use App\Models\User;
use App\Models\Extension;
use App\Models\IVRMenu;

class SearchService
{
    public function search(string $q): array
    {
        $results = [];

        /*
        |--------------------------------------------------------------------------
        | Extensions
        |--------------------------------------------------------------------------
        */
        $extensions = Extension::query()
            ->where('extension', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($extensions as $ext) {
            $results[] = [
                'label' => $ext->extension,
                'description' => $ext->description,
                'type' => 'Extension',
                'icon' => 'bi-telephone',
                'url' => route('extensions.edit', $ext->extension_uuid),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        $users = User::query()
            ->where('username', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'label' => $user->username,
                'description' => 'User',
                'type' => 'User',
                'icon' => 'bi-person',
                'url' => route('users.edit', $user->user_uuid),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | IVR
        |--------------------------------------------------------------------------
        */
        $ivrs = IVRMenu::query()
            ->where('ivr_menu_name', 'like', "%$q%")
            ->limit(5)
            ->get();

        foreach ($ivrs as $ivr) {
            $results[] = [
                'label' => $ivr->ivr_menu_name,
                'description' => 'IVR',
                'type' => 'IVR',
                'icon' => 'bi-diagram-3',
                'url' => route('ivr_menus.edit', $ivr->ivr_uuid),
            ];
        }

        return $results;
    }
}
