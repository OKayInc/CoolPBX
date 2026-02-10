<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Models\Fax;
use App\Models\FaxFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class FaxesFilesTable extends DataTableComponent
{
    protected $model = Fax::class;

    public $fax = null;
    public $viewType = null;
    public $dirFax = null;
    public $dirFaxInbox = null;
    public $dirFaxSent = null;
    public $dirFaxTemp = null;

    public function mount(Fax $fax, $viewType = null)
    {
        $this->fax = $fax;
        $this->viewType = $viewType;

        $this->dirFax = Setting::getSetting('switch', 'storage', 'dir') . '/fax/' . Session::get('domain_name');

        $this->dirFaxInbox = $this->dirFax . '/' . $this->fax->fax_extension . '/inbox';
        $this->dirFaxSent = $this->dirFax . '/' . $this->fax->fax_extension . '/sent';
        $this->dirFaxTemp = $this->dirFax . '/' . $this->fax->fax_extension . '/temp';
    }

    public function configure(): void
    {
        $this->setPrimaryKey('fax_file_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Faxes Files')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [];

        $columns[] = Column::make("Fax uuid", "fax_file_uuid")->hideIf(true);

        $columns[] = Column::make("Caller ID Name", "fax_caller_id_name")
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Caller ID Number", 'fax_caller_id_number')
            ->format(function ($value, $row) {
                return format_phone($value);
            })
            ->sortable();

        if($this->viewType == "sent")
        {
            $columns[] = Column::make("Destination", "fax_destination")
            ->format(function ($value, $row) {
                return format_phone($value);
            })
            ->sortable();
        }

        if(auth()->user()->hasPermission("fax_download_view"))
        {
            $columns[] = Column::make("File", "fax_file_path")
                ->format(function ($value, $row) {
                    $file_name = "";

                    $file = basename($value);

                    if(strtolower(substr($file, -3)) == "tif" || strtolower(substr($file, -3)) == "pdf")
                    {
                        $file_name = substr($file, 0, (strlen($file) -4));
                    }

                    switch($this->viewType)
                    {
                        case "inbox";
                            $dirFax = $this->dirFaxInbox;
                            break;
                        case "sent";
                            $dirFax = $this->dirFaxSent;
                            break;
                    }

                    $path = $dirFax . "/" . $file_name . ".pdf";

                    if(file_exists($path))
                    {
                        switch($this->viewType)
                        {
                            case "inbox";

                                if(auth()->user()->hasPermission("fax_download_view"))
                                {
                                    return '<a href="' . route("faxes.download", [$row->fax_file_uuid]) . '" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>';
                                }

                                break;

                            case "sent";

                                if(auth()->user()->hasPermission("fax_sent_view"))
                                {
                                    return '<a href="' . route("faxes.download", [$row->fax_file_uuid]) . '" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>';
                                }

                                break;
                        }
                    }
                })
                ->html()
                ->sortable();
        }

        $columns[] = Column::make("Date", "fax_epoch")
                ->format(function ($value, $row, Column $column) {
                    return date("D d M Y H:i:s", $value);
                })
                ->sortable();

        return $columns;
    }

    public function builder(): Builder
    {
        $query = FaxFile::query()
            ->where("fax_uuid", $this->fax->fax_uuid)
            ->orderBy('fax_date', 'asc');

        switch($this->viewType)
        {
            case "inbox";
                $query->where("fax_mode", "'rx' \n");
                break;
            case "sent";
                $query->where("fax_mode", "'tx' \n");
                break;
        }

        return $query;
    }
}
