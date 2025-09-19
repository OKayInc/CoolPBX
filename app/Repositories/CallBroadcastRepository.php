<?php

namespace App\Repositories;

use App\Facades\Setting;
use App\Models\CallBroadcast;
use App\Models\Domain;
use App\Models\Recording;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CallBroadcastRepository
{
    protected $callBroadcast;

    public function __construct(CallBroadcast $callBroadcast)
    {
        $this->callBroadcast = $callBroadcast;
    }

    public function all(): Collection
    {
        return $this->callBroadcast->all();
    }

    public function mine(): Collection
    {
        $user = auth()->user();
        return $this->callBroadcast->where('domain_uuid', $user->domain_uuid)->get();
    }

    public function findByUuid(string $callBroadcastUuid, bool $withRelations = false): ?CallBroadcast
    {
        $query = $this->callBroadcast->where('call_broadcast_uuid', $callBroadcastUuid);

        if ($withRelations) {
            $query->with(['domain']);
        }

        return $query->first();
    }

    public function getTotalBroadcastsCount(string $domainUuid): int
    {
        return $this->callBroadcast->where('domain_uuid', $domainUuid)->count();
    }

    public function create(array $broadcastData): CallBroadcast
    {
        $broadcastData['call_broadcast_uuid'] = $broadcastData['call_broadcast_uuid'] ?? Str::uuid();

        try {
            DB::beginTransaction();

            $filteredData = $this->applyBroadcastPermissions($broadcastData);
            $broadcast = $this->callBroadcast->create($filteredData);

            DB::commit();
            return $broadcast;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $callBroadcastUuid, array $broadcastData): CallBroadcast
    {
        try {
            DB::beginTransaction();

            $broadcast = $this->findByUuid($callBroadcastUuid);
            if (!$broadcast) {
                throw new Exception("Call broadcast not found");
            }

            $filteredData = $this->applyBroadcastPermissions($broadcastData, $broadcast);
            $broadcast->update($filteredData);

            DB::commit();
            return $broadcast->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $callBroadcastUuid): void
    {
        try {
            DB::beginTransaction();

            $broadcast = $this->findByUuid($callBroadcastUuid);
            if (!$broadcast) {
                throw new Exception("Call broadcast not found");
            }

            $broadcast->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function copy(string $uuid, string $newName): CallBroadcast
    {
        try {
            DB::beginTransaction();

            $originalBroadcast = $this->findByUuid($uuid);
            if (!$originalBroadcast) {
                throw new Exception("Call broadcast not found");
            }

            $newBroadcast = $originalBroadcast->replicate();
            $newBroadcast->call_broadcast_uuid = Str::uuid();
            $newBroadcast->broadcast_name = $newName;
            $newBroadcast->broadcast_description = $originalBroadcast->broadcast_description . ' (copy)';
            $newBroadcast->save();

            DB::commit();
            return $newBroadcast;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function applyBroadcastPermissions(array $broadcastData, ?CallBroadcast $existingBroadcast = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['domain_uuid'] = $broadcastData['domain_uuid'] ?? ($existingBroadcast->domain_uuid ?? $user->domain_uuid);

        if (is_null($existingBroadcast)) {
            $filteredData['call_broadcast_uuid'] = $broadcastData['call_broadcast_uuid'] ?? Str::uuid();
        }

        $filteredData['broadcast_name'] = $broadcastData['broadcast_name'] ?? ($existingBroadcast->broadcast_name ?? null);
        $filteredData['broadcast_description'] = $broadcastData['broadcast_description'] ?? ($existingBroadcast->broadcast_description ?? null);

        if ($user->hasPermission('call_broadcast_start_time')) {
            if (!empty($broadcastData['broadcast_start_time'])) {
                $startTime = strtotime($broadcastData['broadcast_start_time']);
                $now = time();
                $filteredData['broadcast_start_time'] = $startTime > $now ? ($startTime - $now) : null;
            } else {
                $filteredData['broadcast_start_time'] = $existingBroadcast->broadcast_start_time ?? null;
            }
        }

        if ($user->hasPermission('call_broadcast_accountcode')) {
            if ($user->hasGroup('superadmin')) {
                $filteredData['broadcast_accountcode'] = $broadcastData['broadcast_accountcode'] ?? ($existingBroadcast->broadcast_accountcode ?? null);
            } elseif ($user->hasGroup('admin')) {
                $filteredData['broadcast_accountcode'] = $this->validateAccountCode($broadcastData['broadcast_accountcode'] ?? null, $user->domain_uuid) 
                    ? ($broadcastData['broadcast_accountcode'] ?? ($existingBroadcast->broadcast_accountcode ?? null))
                    : $user->domain_name;
            } else {
                $filteredData['broadcast_accountcode'] = $user->domain_name;
            }
        }

        if ($user->hasPermission('call_broadcast_timeout')) {
            $filteredData['broadcast_timeout'] = $broadcastData['broadcast_timeout'] ?? ($existingBroadcast->broadcast_timeout ?? null);
        }

        if ($user->hasPermission('call_broadcast_concurrent_limit')) {
            $filteredData['broadcast_concurrent_limit'] = $broadcastData['broadcast_concurrent_limit'] ?? ($existingBroadcast->broadcast_concurrent_limit ?? null);
        }

        $filteredData['recording_uuid'] = $broadcastData['recording_uuid'] ?? ($existingBroadcast->recording_uuid ?? null);

        if ($user->hasPermission('call_broadcast_caller_id')) {
            $filteredData['broadcast_caller_id_name'] = $broadcastData['broadcast_caller_id_name'] ?? ($existingBroadcast->broadcast_caller_id_name ?? null);
            $filteredData['broadcast_caller_id_number'] = $broadcastData['broadcast_caller_id_number'] ?? ($existingBroadcast->broadcast_caller_id_number ?? null);
        }

        $filteredData['broadcast_destination_type'] = $broadcastData['broadcast_destination_type'] ?? ($existingBroadcast->broadcast_destination_type ?? null);
        
        if ($user->hasPermission('call_broadcast_destination_number')) {
            $filteredData['broadcast_destination_data'] = $broadcastData['broadcast_destination_data'] ?? ($existingBroadcast->broadcast_destination_data ?? null);
        }

        if ($user->hasPermission('call_broadcast_phone_numbers')) {
            $phoneNumbers = $broadcastData['broadcast_phone_numbers'] ?? ($existingBroadcast->broadcast_phone_numbers ?? null);
            
            if (isset($broadcastData['broadcast_phone_numbers_file'])) {
                $uploadedNumbers = $this->processPhoneNumbersFile($broadcastData['broadcast_phone_numbers_file']);
                if ($uploadedNumbers) {
                    $phoneNumbers = !empty($phoneNumbers) ? $phoneNumbers . "\n" . $uploadedNumbers : $uploadedNumbers;
                }
            }
            
            $filteredData['broadcast_phone_numbers'] = $phoneNumbers;
        }

        if ($user->hasPermission('call_broadcast_voicemail_detection')) {
            $filteredData['broadcast_avmd'] = $broadcastData['broadcast_avmd'] ?? ($existingBroadcast->broadcast_avmd ?? 'false');
        }

        if ($user->hasPermission('call_broadcast_toll_allow')) {
            $filteredData['broadcast_toll_allow'] = $broadcastData['broadcast_toll_allow'] ?? ($existingBroadcast->broadcast_toll_allow ?? null);
        }

        return $filteredData;
    }

    private function validateAccountCode(?string $accountCode, string $domainUuid): bool
    {
        if (empty($accountCode)) {
            return false;
        }

        if (!file_exists(base_path('app/Billing'))) {
            return true;
        }

        try {
            $count = DB::table('v_billings')
                ->where('domain_uuid', $domainUuid)
                ->where('type_value', $accountCode)
                ->count();

            return $count > 0;
        } catch (Exception $e) {
            return true; 
        }
    }

    private function processPhoneNumbersFile(array $fileData): ?string
    {
        if (empty($fileData['tmp_name']) || $fileData['size'] <= 0) {
            return null;
        }

        $allowedTypes = [
            'application/octet-stream',
            'application/vnd.ms-excel',
            'text/plain',
            'text/csv',
            'text/tsv'
        ];

        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        $uploadCsv = '';
        $file = fopen($fileData['tmp_name'], "r");
        $count = 0;

        while (($getData = fgetcsv($file, 0, "\n")) !== false) {
            $count++;
            if ($count == 1) {
                continue; 
            }

            $getData = preg_split('/[ ,|]/', $getData[0], null, PREG_SPLIT_NO_EMPTY);
            $separator = $getData[0] ?? '';
            $separator .= (isset($getData[1]) && $getData[1] != '') ? '|' . $getData[1] : '';
            $separator .= (isset($getData[2]) && $getData[2] != '') ? ',' . $getData[2] : '';
            $separator .= PHP_EOL;
            $uploadCsv .= $separator;
        }

        fclose($file);
        return $uploadCsv;
    }

    public function getRecordings(string $domainUuid): Collection
    {
        try {
            return DB::table('v_recordings')
                ->where('domain_uuid', $domainUuid)
                ->select('recording_uuid', 'recording_name', 'recording_description')
                ->orderBy('recording_name')
                ->get();
        } catch (Exception $e) {
            return collect([]);
        }
    }

    public function getDestinations(string $domainUuid): Collection
    {
        try {
            return DB::table('v_destinations')
                ->where('domain_uuid', $domainUuid)
                ->where('destination_type', 'inbound')
                ->select('destination_uuid', 'destination_number', 'destination_caller_id_name', 'destination_caller_id_number', 'destination_description')
                ->orderBy('destination_number')
                ->get();
        } catch (Exception $e) {
            return collect([]);
        }
    }

    public function getBroadcastsByDomain(string $domainUuid, int $limit = null, int $offset = null): Collection
    {
        $query = $this->callBroadcast->where('domain_uuid', $domainUuid);
        
        if ($limit) {
            $query->limit($limit);
        }
        
        if ($offset) {
            $query->offset($offset);
        }
        
        return $query->orderBy('broadcast_name')->get();
    }

    public function searchBroadcasts(string $domainUuid, string $search): Collection
    {
        return $this->callBroadcast
            ->where('domain_uuid', $domainUuid)
            ->where(function ($query) use ($search) {
                $query->where('broadcast_name', 'like', "%{$search}%")
                      ->orWhere('broadcast_description', 'like', "%{$search}%");
            })
            ->orderBy('broadcast_name')
            ->get();
    }

    public function getActiveBroadcasts(string $domainUuid): Collection
    {
        return $this->callBroadcast
            ->where('domain_uuid', $domainUuid)
            ->whereNotNull('broadcast_start_time')
            ->where('broadcast_start_time', '>', 0)
            ->get();
    }

    public function formatStartTimeForDisplay(CallBroadcast $broadcast): ?string
    {
        if (!$broadcast->broadcast_start_time) {
            return null;
        }

        $referenceDate = $broadcast->update_date ?: $broadcast->insert_date;
        
        if ($referenceDate) {
            $startDateTime = strtotime($referenceDate) + $broadcast->broadcast_start_time;
            return date('Y-m-d H:i', $startDateTime);
        }
        
        return null;
    }
}