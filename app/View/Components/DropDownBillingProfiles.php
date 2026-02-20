<?php

namespace App\View\Components;

use App\Models\BillingProfile;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropDownBillingProfiles extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "", $selected = null, $exclude = null)
    {
        $this->name = $name;
        $this->selected = $selected;

		$billingProfiles = BillingProfile::parentProfiles($exclude);

        $values = [];

		foreach($billingProfiles as $billingProfile)
		{
			$values[] = [
				"id" => $billingProfile->billing_uuid,
				"name" => $billingProfile->contact_organization . " " .  $billingProfile->contact_name_family . " " . $billingProfile->contact_name_given
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
        return view('components.drop-down-billing-profiles');
    }
}
