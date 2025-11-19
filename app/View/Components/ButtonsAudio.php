<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonsAudio extends Component
{
    public $urlPlay;
    public $urlDownload;

    public function __construct($urlPlay = "", $urlDownload = "")
    {
        $this->urlPlay = $urlPlay;
        $this->urlDownload = $urlDownload;
    }

    public function render(): View|Closure|string
    {
        return view('components.buttons-audio');
    }
}
