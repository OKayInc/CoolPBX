<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\VoicemailGreeting;
use App\Models\Voicemail;
use Illuminate\Database\Eloquent\Builder;

class VoicemailGreetingTable extends DataTableComponent
{
    protected $model = VoicemailGreeting::class;
    public string $voicemailId;
    public string $voicemailUuid;
    public ?int $selectedGreetingId = null;

    protected $listeners = [
        'greetingUpdated' => '$refresh',
        'refresh' => '$refresh', 
    ];

    public function mount(string $voicemailId, string $voicemailUuid)
    {
        $this->voicemailId = $voicemailId;
        $this->voicemailUuid = $voicemailUuid;

        $voicemail = Voicemail::where('voicemail_id', $voicemailId)->first();
        if ($voicemail) {
            $this->selectedGreetingId = $voicemail->greeting_id;
        }
    }

    public function configure(): void
    {
        $this->setPrimaryKey('voicemail_greeting_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search greetings')
            ->setPerPageAccepted([10, 25, 50])
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Greeting UUID", "voicemail_greeting_uuid")
                ->hideIf(true),

            Column::make("Selected", "greeting_id")
                ->label(fn($row) => $this->renderSelectedColumn($row))
                ->html()
                ->excludeFromColumnSelect(),

            Column::make("Name", "greeting_name")
                ->sortable()
                ->searchable(),

            Column::make("Tools", "voicemail_greeting_uuid")
                ->label(fn($row) => $this->renderToolsColumn($row))
                ->html()
                ->excludeFromColumnSelect(),

            Column::make("Size", "greeting_filename")
                ->label(fn($row) => $this->renderSizeColumn($row))
                ->html(),
        ];

        if (session('voicemail.storage_type.text') != 'base64') {
            $columns[] = Column::make("Filename", "greeting_filename")
                ->sortable()
                ->searchable();

            $columns[] = Column::make("Uploaded", "greeting_filename")
                ->label(fn($row) => $this->renderUploadedColumn($row))
                ->html();
        }


        $columns[] = Column::make("Description", "greeting_description")
            ->sortable()
            ->searchable()
            ->format(fn($value) => $value ?: '—');

        $columns[] =  Column::make("Actions", "voicemail_greeting_uuid")
            ->label(fn($row) => $this->renderActionsColumn($row))
            ->html()
            ->excludeFromColumnSelect();

        return $columns;
    }

    protected function renderSelectedColumn($row): string
    {
        $checked = ($row->greeting_id == $this->selectedGreetingId) ? 'checked' : '';

        return '<input type="radio"
               name="active_greeting"
               value="' . $row->greeting_id . '"
               wire:click="setActiveGreeting(\'' . $row->voicemail_greeting_uuid . '\')"
               class="form-check-input"
               style="cursor: pointer; width: 20px; height: 20px;"
               ' . $checked . '>';
    }

    public function setActiveGreeting($greetingUuid)
    {
        $greeting = VoicemailGreeting::where('voicemail_greeting_uuid', $greetingUuid)
            ->where('voicemail_id', $this->voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $voicemail = Voicemail::where('voicemail_id', $this->voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $voicemail->greeting_id = $greeting->greeting_id;
        $voicemail->save();

        $this->selectedGreetingId = $greeting->greeting_id;

        session()->flash('message', 'Greeting set as active');

        $this->dispatch('refresh');
    }
    protected function renderActionsColumn($row): string
    {
        $canEdit = auth()->user()->hasPermission('voicemail_greeting_edit');

        if (!$canEdit) {
            return '—';
        }

        return '<button type="button" 
                    wire:click="$dispatch(\'editGreeting\', { greetingUuid: \'' . $row->voicemail_greeting_uuid . '\' })" 
                    class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i>
            </button>';
    }

    protected function renderToolsColumn($row): string
    {
        $uuid = $row->voicemail_greeting_uuid;
        $playUrl = route('voicemails.greetings.play', [
            'voicemailId' => $this->voicemailId,
            'greetingUuid' => $row->voicemail_greeting_uuid,
        ]);

        $downloadUrl = route('voicemails.greetings.download', [
            'voicemailId' => $this->voicemailId,
            'greetingUuid' => $row->voicemail_greeting_uuid,
        ]);

        $html = '<div class="tools-container">';

        $html .= '<div class="progress-bar" data-uuid="' . $uuid . '" style="background-color: #0d6efd; width: 0; height: 3px; position: relative; margin: 5px 0; display: none;"></div>';

        $html .= '<audio class="greeting-audio" 
                 data-uuid="' . $uuid . '" 
                 style="display: none;" 
                 preload="none" 
                 src="' . $playUrl . '"></audio>';

        $html .= '<div class="d-flex gap-2" style="white-space: nowrap;">';

        $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-play-greeting" data-uuid="' . $uuid . '" title="Play / Pause">
                        <i class="fas fa-play"></i>
                    </button>';

        $html .= '<a href="' . $downloadUrl . '" class="btn btn-sm btn-outline-primary" title="Download">
                        <i class="fas fa-download"></i>
                    </a>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function renderSizeColumn($row): string
    {
        if (session('voicemail.storage_type.text') == 'base64') {
            $size = strlen($row->greeting_base64);
        } else {
            $greetingDir = storage_path("app/public/voicemail") . '/' .
                'default' . '/' .
                $row->domain->domain_name . '/' .
                $row->voicemail->voicemail_id;

            $filePath = $greetingDir . '/' . $row->greeting_filename;
            $size = file_exists($filePath) ? filesize($filePath) : 0;
        }

        if ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' B';
        }
    }

    protected function renderUploadedColumn($row): string
    {
        $greetingDir = storage_path("app/public/voicemail") . '/' .
            'default' . '/' .
            $row->domain->domain_name . '/' .
            $row->voicemail->voicemail_id;

        $filePath = $greetingDir . '/' . $row->greeting_filename;

        if (file_exists($filePath)) {
            return date("M d, Y H:i:s", filemtime($filePath));
        }

        return '—';
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('voicemail_greeting_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        return $bulkActions;
    }

    public function bulkDelete(): void
    {
        if (!auth()->user()->hasPermission('voicemail_greeting_delete')) {
            session()->flash('error', 'You do not have permission to delete greetings');
            return;
        }

        $selectedRows = $this->getSelected();

        try {
            $greetings = VoicemailGreeting::whereIn('voicemail_greeting_uuid', $selectedRows)->get();

            foreach ($greetings as $greeting) {
                if (session('voicemail.storage_type.text') != 'base64') {
                    $greetingDir = storage_path("app/public/voicemail") . '/' .
                        'default' . '/' .
                        $greeting->domain->domain_name . '/' .
                        $greeting->voicemail->voicemail_id;

                    $filePath = $greetingDir . '/' . $greeting->greeting_filename;

                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }

                $greeting->delete();
            }

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Greetings deleted successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting greetings: ' . $e->getMessage());
        }
    }

    public function builder(): Builder
    {
        return VoicemailGreeting::query()
            ->select('v_voicemail_greetings.*')
            ->where('voicemail_id', $this->voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->with(['voicemail', 'domain']);
    }
}
