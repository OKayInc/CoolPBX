<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class DestinationExport extends Component
{
	public $selectedColumnGroups = [];
	public $selectAllDestinations = false;

	public $availableColumns = [
		'destinations' => [
			'destination_uuid' => 'UUID',
			'domain_uuid' => 'Domain',
			'dialplan_uuid' => 'Dialplan',
			'fax_uuid' => 'Fax',
			'destination_type' => 'Type',
			'destination_number' => 'Number',
			'destination_trunk_prefix' => 'Trunk prefix',
			'destination_area_code' => 'Area code',
			'destination_prefix' => 'Prefix',
			'destination_condition_field' => 'Condition',
			'destination_number_regex' => 'Number regex',
			'destination_caller_id_name' => 'Caller ID name',
			'destination_caller_id_number' => 'Caller ID number',
			'destination_cid_name_prefix' => 'CID name prefix',
			'destination_context' => 'Context',
			'destination_record' => 'Record',
			'destination_hold_music' => 'Hold music',
			'destination_accountcode' => 'Account code',
			'destination_type_voice' => 'Type voice',
			'destination_type_fax' => 'Type fax',
			'destination_type_text' => 'Type text',
			'destination_type_emergency' => 'Type emergency',
			'destination_app' => 'App',
			'destination_data' => 'Data',
			'destination_alternate_app' => 'Alternate app',
			'destination_alternate_data' => 'Alternate data',
			'destination_enabled' => 'Enabled',
			'destination_description' => 'Description',
			'destination_order' => 'Order',
		]
	];

	public function mount()
	{
		foreach (array_keys($this->availableColumns) as $table)
		{
			$this->selectedColumnGroups[$table] = [];
		}
	}

	public function updatedSelectAllDestinations()
	{
		if ($this->selectAllDestinations)
		{
			$this->selectedColumnGroups['destinations'] = array_keys($this->availableColumns['destinations']);
		}
		else
		{
			$this->selectedColumnGroups['destinations'] = [];
		}
	}

	public function updatedSelectedColumnGroups()
	{
		$this->selectAllDestinations = count($this->selectedColumnGroups['destinations']) === count($this->availableColumns['destinations']);
	}

	public function exportDestinations()
	{
		$totalSelected = array_sum(array_map('count', $this->selectedColumnGroups));

		if ($totalSelected === 0)
		{
			session()->flash('error', 'Please select at least one column to export.');
			return;
		}

		$validatedColumns = [];

		foreach ($this->selectedColumnGroups as $table => $columns)
		{
			if (!empty($columns) && isset($this->availableColumns[$table]))
			{
				$validatedColumns[$table] = array_intersect($columns, array_keys($this->availableColumns[$table]));
			}
		}

		if (empty($validatedColumns))
		{
			session()->flash('error', 'Invalid columns selected.');

			return;
		}

		return $this->downloadCsv($validatedColumns);
	}

	private function downloadCsv($columnGroups)
	{
		$fileName = 'destination_export_' . date('Y-m-d') . '.csv';

		return response()->streamDownload(function () use ($columnGroups)
		{
			$handle = fopen('php://output', 'w');

			$headers = [];

			foreach ($columnGroups as $table => $columns)
			{
				foreach ($columns as $column)
				{
					$headers[] = $this->availableColumns[$table][$column] ?? $column;
				}
			}

			fputcsv($handle, $headers);

			$destinationsColumns = $columnGroups['destinations'] ?? [];

			if (!empty($destinationsColumns))
			{
				$query = Destination::where('domain_uuid', auth()->user()->domain_uuid);

				$query->select($destinationsColumns);

				$query->chunk(1000, function ($destinations) use ($handle, $columnGroups)
				{
					foreach ($destinations as $destination)
					{
						$row = [];

						if (!empty($columnGroups['destinations']))
						{
							foreach ($columnGroups['destinations'] as $column)
							{
								$row[] = $destination->$column ?? '';
							}
						}

						fputcsv($handle, $row);
					}
				});
			}

			fclose($handle);
		}, $fileName, [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
		]);
	}

	public function getTotalSelectedColumns()
	{
		return array_sum(array_map('count', $this->selectedColumnGroups));
	}

	public function getTotalAvailableColumns()
	{
		return array_sum(array_map('count', $this->availableColumns));
	}

	public function goBack()
	{
		return redirect()->route('destinations.index');
	}

	public function render()
	{
		return view('livewire.destination-export');
	}
}
