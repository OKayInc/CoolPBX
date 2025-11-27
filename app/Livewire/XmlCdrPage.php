<?php

namespace App\Livewire;

use App\Models\XmlCDR;
use Livewire\Attributes\On;
use Livewire\Component;

class XmlCdrPage extends Component
{
    public $filters = [];
    public $showModal = false;
    public $xml_cdr_uuid = null;
    public $tags = "";

    #[On("open-edit-tags-modal")]
    public function openEditTagsModal($xml_cdr_uuid)
    {
        $this->xml_cdr_uuid = $xml_cdr_uuid;

        $record = XmlCDR::find($xml_cdr_uuid);

        $this->tags = $record ? $record->tags : "";

        $this->showModal = true;
    }

    public function saveTags()
    {
        if($this->xml_cdr_uuid)
        {
            XmlCDR::where("xml_cdr_uuid", $this->xml_cdr_uuid)->update(["tags" => $this->tags]);
        }

        $this->reset(["showModal", "xml_cdr_uuid", "tags"]);
    }

    public function mount($filters = [])
    {
        $this->filters = $this->initFilters();

        $this->filters = array_merge($this->filters, $filters);
    }

    private function initFilters()
    {
        return [
            "type" => [],
        ];
    }

    public function resetFilters()
    {
        $this->filters = $this->initFilters();
    }

    public function applyFilters() {}

    public function render()
    {
        return view("livewire.xml-cdr-page");
    }
}
