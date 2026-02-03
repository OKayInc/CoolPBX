<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class SwitchUsers extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "", $selected = null, $all = false)
    {
        $this->name = $name;
        $this->selected = $selected;

		$query = User::query();

		if(!$all)
		{
			$query->where('domain_uuid', Session::get('domain_uuid'));
		}

		$users = $query->orderBy('username')->get();

        $domains = [];
		$domain = "";

		foreach($users as $user)
		{
			$domain = $user->domain->domain_name;

			$domains[$domain][] = ["id" => $user->user_uuid, "name" => $user->username];
		}

		foreach($domains as $domain => $users)
		{
        	$this->setOptions($domain, $users);
		}

        $this->options = json_decode(json_encode($this->options));
    }

    private function setOptions($group, $values)
    {
        $this->options[] = [
            "label" => __($group),
            "values" => $values
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.switch-users');
    }
}
