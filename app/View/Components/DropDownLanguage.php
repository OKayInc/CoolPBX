<?php

namespace App\View\Components;

use App\Models\Language;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropDownLanguage extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "", $selected = null)
    {
        $this->name = $name;
        $this->selected = $selected;

        $languages = Language::orderBy('language')->get();

        $values = [];

        foreach ($languages as $language) {
            $values[] = [
                "id" => $language->code,
                "name" => $language->language . " [" . $language->code . "]",
            ];
        }

        $this->setOptions("", $values);

        $this->options = json_decode(json_encode($this->options));
    }

    private function setOptions($group, $values)
    {
        $this->options[] = [
            "label" => __($group),
            "values" => $values,
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.drop-down-language');
    }
}
