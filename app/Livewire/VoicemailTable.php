<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Voicemail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VoicemailTable extends DataTableComponent
{
    protected $model = Voicemail::class;
    protected bool $showAll = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('voicemail_edit');
        $tableConfig = $this->setPrimaryKey('voicemail_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search voicemails')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('voicemails.edit', $row->voicemail_uuid);
            });
        }

        if (request('showAll')) {
            $this->showAll = true;
        }
    }

    public function columns(): array
    {
        $columns = [];
        $columns = [
            Column::make("Voicemail uuid", "voicemail_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Voicemail id", "voicemail_id")
                ->sortable()
                ->searchable(),
            Column::make("Mail to", "voicemail_mail_to")
                ->sortable()
                ->searchable(),
            Column::make("Attached", "voicemail_file")
                ->sortable()
                ->format(function ($value, $row) {
                    if ($value == 'attach' || $value) {
                        return '<span class="text-success"><i class="fas fa-check-circle"></i></span>';
                    }
                    return '<span class="text-danger"><i class="fas fa-times-circle"></i></span>';
                })
                ->html(),
            Column::make("Tools")
                ->label(fn($row) => $this->renderToolsColumn($row))
                ->html()
                ->excludeFromColumnSelect(),
            Column::make("Keep Local", "voicemail_local_after_email")
                ->sortable()
                ->searchable(),
            Column::make("Enabled", "voicemail_enabled")
                ->sortable()
                ->searchable(),
            Column::make("Voicemail description", "voicemail_description")
                ->sortable()
                ->searchable(),
        ];

        if ($this->showAll) {
            array_splice($columns, 3, 0, [
                Column::make("Domain", "domain.domain_name")
                    ->sortable()
                    ->searchable()
            ]);
        }
        return $columns;
    }


    //TODO:  Reemplazar por ruta correspondiente en devices.index (greetings y messages)
    protected function renderToolsColumn($row): string
    {
        $html = '<div class="d-flex gap-2" style="white-space: nowrap;">';

        if (auth()->user()->hasPermission('voicemail_greeting_view')) {
            $greetingsUrl = route('voicemails.greetings.index', [
                'voicemailId' => $row->voicemail_id
            ]);
            $html .= '<a href="' . $greetingsUrl . '" class="btn btn-sm btn-outline-primary">Greetings</a>';
        }

        if (auth()->user()->hasPermission('voicemail_message_view')) {
            $messageCount = $row->voicemailmessages()->count();

            $messagesUrl = route('voicemails.messages.index', ['voicemailUuid' => $row->voicemail_uuid]);
            $html .= '<a href="' . $messagesUrl . '" class="btn btn-sm btn-outline-primary">Messages (' . $messageCount . ')</a>';
        }

        $html .= '</div>';

        return $html;
    }


    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('voicemail_edit')) {
            $bulkActions['toggleVoicemail'] = 'Toggle';
        }

        if (auth()->user()->hasPermission('device_profile_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('device_profile_add')) {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function toggleVoicemail(): void
    {
        $selectRows = $this->getSelected();

        Voicemail::whereIn('device_uuid', $selectRows)
            ->update([
                'voicemail_enabled' => DB::raw("CASE WHEN voicemail_enabled = 'true' THEN 'false' ELSE 'true' END")
            ]);

        $this->clearSelected();
        $this->dispatch('refresh');

        session()->flash('message', 'Voicemail status toggled successfully');
    }

    //todo: fijarse en el codigo viejo como funciona esto si borra mas entidades relacionadas.
    public function bulkDelete(): void
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            Voicemail::whereIn('voicemail_uuid', $selectRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Voicemails deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while deleting voicemails: ' . $e->getMessage());
        }
    }



    public function builder(): Builder
    {
        $query = Voicemail::query();

        if ($this->showAll) {
            $query->leftJoin('v_domains', 'v_voicemails.domain_uuid', '=', 'v_domains.domain_uuid')
                ->select('v_voicemails.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_voicemails.domain_uuid', auth()->user()->domain_uuid)
                    ->orWhereNull('v_voicemails.domain_uuid');
            });
        }

        return $query;
    }
}
