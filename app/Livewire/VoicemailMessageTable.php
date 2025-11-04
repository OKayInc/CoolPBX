<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\VoicemailMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VoicemailMessageTable extends DataTableComponent
{
    protected $model = VoicemailMessage::class;
    public string $voicemailUuid;

    public function configure(): void
    {
        $this->setPrimaryKey('voicemail_message_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search voicemail messages')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Voicemail message uuid", "voicemail_message_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Voicemail uuid", "voicemail_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Received", "created_epoch")
                ->sortable()
                ->format(function ($value, $row) {
                    $isNew = ($row->message_status != 'saved');
                    $style = $isNew ? 'font-weight: bold;' : '';
                    $date = date('M d, Y H:i:s', $value);
                    return '<span style="' . $style . '">' . $date . '</span>';
                })
                ->html(),
            Column::make("Caller ID Name", "caller_id_name")
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $isNew = ($row->message_status != 'saved');
                    $style = $isNew ? 'font-weight: bold;' : '';
                    return '<span style="' . $style . '">' . e($value) . '</span>';
                })
                ->html(),
            Column::make("Caller ID Number", "caller_id_number")
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $isNew = ($row->message_status != 'saved');
                    $style = $isNew ? 'font-weight: bold;' : '';
                    return '<span style="' . $style . '">' . e($value) . '</span>';
                })
                ->html(),
            Column::make("Tools", "voicemail_message_uuid")
                ->label(fn($row) => $this->renderToolsColumn($row))
                ->html()
                ->excludeFromColumnSelect(),
            Column::make("Length", "message_length")
                ->sortable()
                ->format(function ($value, $row) {
                    $isNew = ($row->message_status != 'saved');
                    $style = $isNew ? 'font-weight: bold;' : '';
                    $minutes = floor($value / 60);
                    $seconds = $value % 60;
                    $time = sprintf('%02d:%02d', $minutes, $seconds);
                    return '<span style="' . $style . '">' . $time . '</span>';
                })
                ->html(),
        ];

        if (session('voicemail.storage_type.text') != 'base64') {
            $columns[] = Column::make("Size", "message_base64")
                ->sortable()
                ->format(function ($value, $row) {
                    $isNew = ($row->message_status != 'saved');
                    $style = $isNew ? 'font-weight: bold;' : '';
                    if ($value >= 1048576) {
                        $size = number_format($value / 1048576, 2) . ' MB';
                    } elseif ($value >= 1024) {
                        $size = number_format($value / 1024, 2) . ' KB';
                    } else {
                        $size = $value . ' B';
                    }
                    return '<span style="' . $style . '">' . $size . '</span>';
                })
                ->html();
        }

        return $columns;
    }

    protected function renderToolsColumn($row): string
    {
        $uuid = $row->voicemail_message_uuid;
        $playUrl = route('voicemails.messages.play', [
            'voicemailUuid' => $row->voicemail_uuid,
            'voicemailMessageUuid' => $row->voicemail_message_uuid,
        ]);

        $downloadUrl = route('voicemails.messages.download', [
            'voicemailUuid' => $row->voicemail_uuid,
            'voicemailMessageUuid' => $row->voicemail_message_uuid,
        ]);


        $html = '<div class="tools-container">';

        $html .= '<div class="progress-bar" data-uuid="' . $uuid . '" style="background-color: #0d6efd; width: 0; height: 3px; position: relative; margin: 5px 0; display: none;"></div>';

        $html .= '<audio class="voicemail-audio" 
                 data-uuid="' . $uuid . '" 
                 data-voicemail-uuid="' . $row->voicemail_uuid . '" 
                 style="display: none;" 
                 preload="none" 
                 src="' . $playUrl . '"></audio>';

        $html .= '<div class="d-flex gap-2" style="white-space: nowrap;">';

        $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-play-voicemail" data-uuid="' . $uuid . '" title="Play / Pause">
                        <i class="fas fa-play"></i>
                    </button>';


        $html .= '<a href="' . $downloadUrl . '" class="btn btn-sm btn-outline-primary" title="Download" onclick="markAsRead(\'' . $uuid . '\')">
                        <i class="fas fa-download"></i>
                    </a>';


        if (session('voicemail.transcribe_enabled.boolean') == 'true' && !empty($row->message_transcription)) {
            $html .= '<button type="button" class="btn btn-sm btn-outline-info btn-toggle-transcription" data-uuid="' . $uuid . '" title="Transcription">
                        <i class="fas fa-quote-right"></i>
                    </button>';
        }

        $html .= '</div>'; 
        $html .= '</div>';

        return $html;
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('voicemail_message_edit')) {
            $bulkActions['toggleMessages'] = 'Toggle';
        }

        if (auth()->user()->hasPermission('voicemail_message_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        return $bulkActions;
    }

    public function toggleMessages(): void
    {
        $selectedRows = $this->getSelected();

        VoicemailMessage::whereIn('voicemail_message_uuid', $selectedRows)
            ->update([
                'message_status' => DB::raw("CASE WHEN message_status = 'saved' THEN '' ELSE 'saved' END")
            ]);

        $this->clearSelected();
        $this->dispatch('refresh');

        session()->flash('message', 'Message status toggled successfully');
    }

    public function bulkDelete(): void
    {
        $selectedRows = $this->getSelected();

        try {
            DB::beginTransaction();

            VoicemailMessage::whereIn('voicemail_message_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Messages deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while deleting messages: ' . $e->getMessage());
        }
    }

    public function builder(): Builder
    {
        $query = VoicemailMessage::query();

        $this->voicemailUuid = request('voicemailUuid');

        if (request('voicemailUuid')) {
            $query->where('voicemail_uuid', $this->voicemailUuid);
        }

        if (!auth()->user()->hasPermission('voicemail_message_view_all')) {
            $query->where('domain_uuid', auth()->user()->domain_uuid);
        }

        $query->orderBy('created_epoch', 'desc');

        return $query;
    }
}
