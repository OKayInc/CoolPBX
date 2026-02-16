<?php

namespace App\View\Components;

use App\Models\Domain;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class DropDownDomains extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "", $selected = null, $parentDomain = null)
    {
        $this->name = $name;
        $this->selected = $selected;

		$query = Domain::query();

		if(!empty($parentDomain))
		{
			$query->where('domain_parent_uuid', $parentDomain);
		}

		$domains = $query->orderBy('domain_name')->get();

        $values = [];

		foreach($domains as $domain)
		{
			$values[] = [
				"id" => $domain->domain_uuid,
				"name" => $domain->domain_name
			];
		}

        $this->setOptions("", $values);

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
        return view('components.drop-down-domains');
    }
}
