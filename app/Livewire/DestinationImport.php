<?php

namespace App\Livewire;

use App\Models\Destination;
use App\Repositories\DestinationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class DestinationImport extends Component
{
	use WithFileUploads;

	public $step = 1;
	public $data = '';
	public $uploadedFile;
	public $fromRow = 2;
	public $delimiter = 'comma';
	public $enclosure = 'quote';
	public $csvData = [];
	public $headers = [];
	public $fieldMappings = [];
	public $availableFields = [];
	public $importResults = [];

	protected $rules = [
		'data' => 'required_without:uploadedFile',
		'uploadedFile' => 'required_without:data',
		'fromRow' => 'required|integer|min:1|max:99',
		'delimiter' => 'required|in:comma,pipe,semicolon,tab',
		'enclosure' => 'required|in:quote,none',
	];

	protected $destinationRepository;

    public function boot(DestinationRepository $destinationRepository)
    {
        $this->destinationRepository = $destinationRepository;
    }

	public function mount()
	{
		$this->setupAvailableFields();
	}

	public function setupAvailableFields()
	{
		$this->availableFields = [
			'destinations' => [
				'dialplan_uuid',
				'fax_uuid',
				'user_uuid',
				'group_uuid',
				'destination_type',
				'destination_number',
				'destination_trunk_prefix',
				'destination_area_code',
				'destination_prefix',
				'destination_condition_field',
				'destination_number_regex',
				'destination_caller_id_name',
				'destination_caller_id_number',
				'destination_cid_name_prefix',
				'destination_context',
				'destination_record',
				'destination_hold_music',
				'destination_distinctive_ring',
				'destination_accountcode',
				'destination_type_voice',
				'destination_type_fax',
				'destination_type_emergency',
				'destination_type_text',
				'destination_conditions',
				'destination_actions',
				'destination_app',
				'destination_data',
				'destination_alternate_app',
				'destination_alternate_data',
				'destination_order',
				'destination_enabled',
				'destination_description',
			],
		];
	}

	public function continue()
	{
		$this->validate();

		if ($this->step == 1)
		{
			$this->processUpload();
		}
		elseif ($this->step == 2)
		{
			$this->importData();
		}
	}

	private function processUpload()
	{
		try
		{
			if ($this->uploadedFile)
			{
				$csvContent = file_get_contents($this->uploadedFile->getRealPath());
			}
			else
			{
				$csvContent = $this->data;
			}

			$filename = 'destinations-import-' . Str::random(10) . '.csv';

			Storage::disk('local')->put('temp/' . $filename, $csvContent);

			$this->processCsvData($csvContent);

			$this->step = 2;
		}
		catch (\Exception $e)
		{
			session()->flash('error', 'Error al procesar el archivo: ' . $e->getMessage());
		}
	}

	private function processCsvData($csvContent)
	{
		$delimiter = $this->getDelimiterChar();
		$enclosure = $this->getEnclosureChar();

		$lines = explode("\n", $csvContent);
		$this->csvData = [];

		foreach ($lines as $index => $line)
		{
			if ($index >= ($this->fromRow - 1) && !empty(trim($line)))
			{
				$this->csvData[] = str_getcsv($line, $delimiter, $enclosure);
			}
		}

		if (!empty($lines))
		{
			$this->headers = str_getcsv($lines[0], $delimiter, $enclosure);
			$this->headers = array_map(function ($header)
			{
				return preg_replace('/[^a-zA-Z0-9_]/', '', trim($header));
			}, $this->headers);
		}

		$this->initializeFieldMappings();
	}

	private function initializeFieldMappings()
	{
		foreach ($this->headers as $index => $header)
		{
			$this->fieldMappings[$index] = $this->suggestFieldMapping($header);
		}
	}

	private function suggestFieldMapping($header)
	{
		$header = strtolower($header);

		$mappings = [
			'dialplan_uuid' => 'destinations.dialplan_uuid',
			'fax_uuid' => 'destinations.fax_uuid',
			'user_uuid' => 'destinations.user_uuid',
			'group_uuid' => 'destinations.group_uuid',
			'destination_type' => 'destinations.destination_type',
			'destination_number' => 'destinations.destination_number',
			'destination_trunk_prefix' => 'destinations.destination_trunk_prefix',
			'destination_area_code' => 'destinations.destination_area_code',
			'destination_prefix' => 'destinations.destination_prefix',
			'destination_condition_field' => 'destinations.destination_condition_field',
			'destination_number_regex' => 'destinations.destination_number_regex',
			'destination_caller_id_name' => 'destinations.destination_caller_id_name',
			'destination_caller_id_number' => 'destinations.destination_caller_id_number',
			'destination_cid_name_prefix' => 'destinations.destination_cid_name_prefix',
			'destination_context' => 'destinations.destination_context',
			'destination_record' => 'destinations.destination_record',
			'destination_hold_music' => 'destinations.destination_hold_music',
			'destination_distinctive_ring' => 'destinations.destination_distinctive_ring',
			'destination_accountcode' => 'destinations.destination_accountcode',
			'destination_type_voice' => 'destinations.destination_type_voice',
			'destination_type_fax' => 'destinations.destination_type_fax',
			'destination_type_emergency' => 'destinations.destination_type_emergency',
			'destination_type_text' => 'destinations.destination_type_text',
			'destination_conditions' => 'destinations.destination_conditions',
			'destination_actions' => 'destinations.destination_actions',
			'destination_app' => 'destinations.destination_app',
			'destination_data' => 'destinations.destination_data',
			'destination_alternate_app' => 'destinations.destination_alternate_app',
			'destination_alternate_data' => 'destinations.destination_alternate_data',
			'destination_order' => 'destinations.destination_order',
			'destination_enabled' => 'destinations.destination_enabled',
			'destination_description' => 'destinations.destination_description',
		];

		return $mappings[$header] ?? '';
	}

	private function getDelimiterChar()
	{
		$delimiters = [
			'comma' => ',',
			'pipe' => '|',
			'semicolon' => ';',
			'tab' => "\t"
		];

		return $delimiters[$this->delimiter] ?? ',';
	}

	private function getEnclosureChar()
	{
		return $this->enclosure === 'quote' ? '"' : '';
	}

	public function importData()
	{
		try
		{
			DB::beginTransaction();

			$importCount = 0;
			$errors = [];
			$fieldCounts = [];

			foreach ($this->csvData as $rowIndex => $row)
			{
				try
				{
					$destinationData = [];

					foreach ($this->fieldMappings as $csvIndex => $mapping)
					{
						if (empty($mapping) || !isset($row[$csvIndex]))
						{
							continue;
						}

						[$table, $field] = explode('.', $mapping);

						$value = trim($row[$csvIndex]);

						if (!isset($fieldCounts[$table][$field]))
						{
							$fieldCounts[$table][$field] = 0;
						}
						else
						{
							$fieldCounts[$table][$field]++;
						}

						$fieldId = $fieldCounts[$table][$field];

						switch ($table)
						{
							case 'destinations':

								$destinationData[$field] = $this->processFieldValue($field, $value);

								break;
						}
					}

					if(!empty($destinationData))
					{
						$destinationData['domain_uuid'] = Session::get('domain_uuid');

						$existingDestination = null;

						if (isset($destinationData['dialplan_uuid']))
						{
							$existingDestination = Destination::where('dialplan_uuid', $destinationData['dialplan_uuid'])
								->where('domain_uuid', Session::get('domain_uuid'))
								->first();
						}

						if ($existingDestination)
						{
							$this->destinationRepository->update($existingDestination, $destinationData);
						}
						else
						{
							$destinationData['destination_uuid'] = Str::uuid();

							$this->destinationRepository->create($destinationData);
						}

						$importCount++;
					}
				}
				catch (\Exception $e)
				{
					$errors[] = "Fila " . ($rowIndex + $this->fromRow) . ": " . $e->getMessage();
				}
			}

			DB::commit();

			$this->importResults = [
				'success' => $importCount,
				'errors' => $errors
			];

			$this->step = 3;
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			session()->flash('error', 'Error durante la importación: ' . $e->getMessage());
		}
	}

	private function processFieldValue($field, $value)
	{
		switch ($field)
		{
			case 'destination_enabled':
				return strtolower($value) === 'true' || $value === '1' ? 'true' : 'false';

			case 'destination_type_voice':
			case 'destination_type_fax':
			case 'destination_type_emergency':
			case 'destination_type_text':
				return ($value) ? 1 : 0;

			default:
				return $value;
		}
	}

	public function resetImport()
	{
		$this->step = 1;
		$this->data = '';
		$this->uploadedFile = null;
		$this->csvData = [];
		$this->headers = [];
		$this->fieldMappings = [];
		$this->importResults = [];
	}

	public function render()
	{
		return view('livewire.destination-import');
	}
}
