<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropDownTimezones extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "", $selected = null)
    {
        $this->name = $name;
        $this->selected = $selected;

        $timezones = timezone_identifiers_list();
        $grouped = [];

        foreach ($timezones as $tz) {
            $parts = explode('/', $tz, 2);
            $region = $parts[0];
            $city = $parts[1] ?? $tz;

            $now = new \DateTime('now', new \DateTimeZone($tz));
            $offset = $now->getOffset();
            $hours = intdiv($offset, 3600);
            $minutes = abs(($offset % 3600) / 60);
            $label = sprintf('(UTC%+03d:%02d) %s', $hours, $minutes, str_replace('_', ' ', $city));

            $grouped[$region][] = [
                'id' => $tz,
                'name' => $label,
            ];
        }

        foreach ($grouped as $region => $values) {
            $this->setOptions($region, $values);
        }

        $this->options = json_decode(json_encode($this->options));
    }

    private function setOptions($group, $values)
    {
        $this->options[] = [
            'label' => __($group),
            'values' => $values,
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.drop-down-timezones');
    }
}
