<?php

namespace App\View\Components;

use App\Facades\Setting;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SwitchCurrencies extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "currency", $selected = null, $p100 = 0)
    {
        $this->name = $name;
        $this->selected = $selected;

        $billingCurrency = Setting::getSetting('billing', 'currency', 'text');

        if(empty($this->selected))
		{
            $this->selected = (strlen($billingCurrency) ? $billingCurrency : 'USD');
        }

        $currencies = config('currencies');

        if($p100)
		{
            $currencies[] = '%';
        }

        $this->setOptions("", $currencies);

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
        return view('components.switch-currencies');
    }
}
