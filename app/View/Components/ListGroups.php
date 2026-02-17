<?php

namespace App\View\Components;

use App\Models\Domain;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class ListGroups extends Component
{
    public $name;
    public $selected;
    public $columns;
    public $options;
    public $type;

    public function __construct($name = "", $selected = null, $multiple = true, $columns = 1, $fullName = true)
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->type = $multiple ? "checkbox" : "radio";

		if($selected instanceof \Illuminate\Support\Collection)
		{
			$selected = $selected->pluck('group_uuid')->toArray();
		}

		$this->selected = is_array($selected) ? $selected : [$selected];

        $currentDomain = Domain::find(Session::get('domain_uuid'));

        $groups = $currentDomain->groups;

        $values = [];

		foreach($groups as $group)
		{
			$values[] = [
				"id" => $group->group_uuid,
				"name" => ($fullName) ? $group->full_group_name : $group->group_name
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
        return view('components.list-groups');
    }
}
