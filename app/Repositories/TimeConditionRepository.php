<?php

namespace App\Repositories;

use App\Models\Dialplan;
use App\Models\DialplanDetail;
use App\Models\DefaultSetting;
use App\Services\DialplanXmlGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Exception;

class TimeConditionRepository
{
    protected DialplanRepository $dialplanRepository;
    protected DialplanDetailRepository $dialplanDetailRepository;
    protected DialplanXmlGenerator $dialplanXmlGenerator;

    public function __construct(
        DialplanRepository $dialplanRepository,
        DialplanDetailRepository $dialplanDetailRepository,
        DialplanXmlGenerator $dialplanXmlGenerator
    ) {
        $this->dialplanRepository = $dialplanRepository;
        $this->dialplanDetailRepository = $dialplanDetailRepository;
        $this->dialplanXmlGenerator = $dialplanXmlGenerator;
    }

    public function getAllDomains(): array
    {
        return DB::table('v_domains')
            ->where('domain_enabled', 'true')
            ->orderBy('domain_name')
            ->get()
            ->map(function ($domain) {
                return [
                    'domain_uuid' => $domain->domain_uuid,
                    'domain_name' => $domain->domain_name,
                ];
            })
            ->toArray();
    }

    public function getAllForDomain(string $domainUuid)
    {
        return Dialplan::where('app_uuid', config('timecondition.time_conditions.app_uuid'))
            ->where(function ($query) use ($domainUuid) {
                $query->where('domain_uuid', $domainUuid)
                    ->orWhereNull('domain_uuid');
            })
            ->orderBy('dialplan_order')
            ->orderBy('dialplan_name')
            ->get();
    }

    public function findByUuid(string $uuid, bool $withDetails = false): ?Dialplan
    {
        $query = Dialplan::where('dialplan_uuid', $uuid)
            ->where('app_uuid', config('timecondition.time_conditions.app_uuid'));

        if ($withDetails) {
            $query->with(['dialplanDetails' => function ($query) {
                $query->orderBy('dialplan_detail_group')
                    ->orderBy('dialplan_detail_order');
            }]);
        }

        return $query->first();
    }

    public function getAvailablePresets(?string $region = null): array
    {
        $region = $region ?? Session::get('time_conditions.region', 'usa');
        $presetCategory = 'preset_' . $region;

        $presets = DefaultSetting::where('default_setting_category', 'time_conditions')
            ->where('default_setting_subcategory', $presetCategory)
            ->where('default_setting_enabled', 'true')
            ->get();

        $availablePresets = [];

        foreach ($presets as $preset) {
            $presetData = json_decode($preset->default_setting_value, true);

            if (is_array($presetData)) {
                foreach ($presetData as $presetName => $presetConditions) {
                    $availablePresets[$presetName] = $presetConditions;
                }
            }
        }

        return $availablePresets;
    }

    public function create(
        array $data,
        array $customConditions = [],
        array $presets = [],
        ?string $defaultPresetAction = null
    ): Dialplan {
        try {
            DB::beginTransaction();

            $dialplanData = [
                'dialplan_uuid' => $data['dialplan_uuid'] ?? Str::uuid(),
                'domain_uuid' => $data['domain_uuid'] ?? Session::get('domain_uuid'),
                'app_uuid' => config('timecondition.time_conditions.app_uuid'),
                'dialplan_name' => str_replace('/', '', $data['dialplan_name']),
                'dialplan_number' => $data['dialplan_number'],
                'dialplan_context' => $data['dialplan_context'] ?? Session::get('domain_name'),
                'dialplan_order' => $data['dialplan_order'] ?? 330,
                'dialplan_enabled' => $data['dialplan_enabled'] ?? 'true',
                'dialplan_description' => $data['dialplan_description'] ?? null,
                'dialplan_continue' => 'false',
            ];

            $dialplan = $this->dialplanRepository->create($dialplanData);

            $details = $this->buildDialplanDetails(
                $dialplan->dialplan_uuid,
                $dialplan->domain_uuid,
                $dialplan->dialplan_number,
                $customConditions,
                $presets,
                $defaultPresetAction,
                $data['dialplan_anti_action'] ?? null
            );

            if (!empty($details)) {
                $this->dialplanDetailRepository->create($dialplan, $details);
            }

            $this->dialplanXmlGenerator->regenerateAndSave($dialplan->dialplan_uuid);

            DB::commit();
            return $dialplan->fresh(['dialplanDetails']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(
        string $uuid,
        array $data,
        array $customConditions = [],
        array $presets = [],
        ?string $defaultPresetAction = null
    ): Dialplan {
        try {
            DB::beginTransaction();

            $dialplan = $this->findByUuid($uuid);
            if (!$dialplan) {
                throw new Exception("Time condition not found");
            }

            $dialplanData = [
                'dialplan_name' => str_replace('/', '', $data['dialplan_name']),
                'dialplan_number' => $data['dialplan_number'],
                'dialplan_context' => $data['dialplan_context'],
                'dialplan_order' => $data['dialplan_order'],
                'dialplan_enabled' => $data['dialplan_enabled'] ?? 'true',
                'dialplan_description' => $data['dialplan_description'] ?? null,
            ];

            if (isset($data['domain_uuid'])) {
                $dialplanData['domain_uuid'] = $data['domain_uuid'];
            }

            $this->dialplanRepository->update($uuid, $dialplanData);

            $this->dialplanDetailRepository->deleteByDialplan($dialplan);

            $details = $this->buildDialplanDetails(
                $dialplan->dialplan_uuid,
                $dialplan->domain_uuid,
                $data['dialplan_number'],
                $customConditions,
                $presets,
                $defaultPresetAction,
                $data['dialplan_anti_action'] ?? null
            );

            if (!empty($details)) {
                $this->dialplanDetailRepository->create($dialplan, $details);
            }

            $this->dialplanXmlGenerator->regenerateAndSave($dialplan->dialplan_uuid);

            DB::commit();
            return $dialplan->fresh(['dialplanDetails']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function delete(string $uuid): bool
    {
        return $this->dialplanRepository->delete($uuid);
    }
    public function copy(string $uuid): Dialplan
    {
        try {
            DB::beginTransaction();

            $original = $this->findByUuid($uuid, true);
            if (!$original) {
                throw new Exception("Time condition not found");
            }

            $newDialplan = $original->replicate();
            $newDialplan->dialplan_uuid = Str::uuid();
            $newDialplan->dialplan_name = $original->dialplan_name . ' (copy)';
            $newDialplan->dialplan_description = ($original->dialplan_description ?? '') . ' (copy)';
            $newDialplan->save();

            foreach ($original->dialplanDetails as $detail) {
                $newDetail = $detail->replicate();
                $newDetail->dialplan_detail_uuid = Str::uuid();
                $newDetail->dialplan_uuid = $newDialplan->dialplan_uuid;
                $newDetail->save();
            }

            $this->dialplanXmlGenerator->regenerateAndSave($newDialplan->dialplan_uuid);

            DB::commit();
            return $newDialplan->fresh(['dialplanDetails']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggle(string $uuid): bool
    {
        $dialplan = $this->findByUuid($uuid);
        if (!$dialplan) {
            return false;
        }

        return $this->dialplanRepository->update($uuid, [
            'dialplan_enabled' => $dialplan->dialplan_enabled === 'true' ? 'false' : 'true'
        ]);
    }

    protected function buildDialplanDetails(
        string $dialplanUuid,
        ?string $domainUuid,
        string $dialplanNumber,
        array $customConditions,
        ?array $presets,
        ?string $defaultPresetAction,
        ?string $antiAction
    ): array {
        $details = [];

        if (!empty($customConditions)) {
            foreach ($customConditions as $groupId => $group) {
                if (empty($group['conditions']) || empty($group['action'])) {
                    continue;
                }

                $detailGroup = is_numeric($groupId) ? $groupId : 500;
                $detailOrder = 0;

                $isPreset = $group['is_preset'] ?? false;
                $presetName = $group['preset_name'] ?? null;

                $detailOrder += 10;
                $details[] = [
                    'dialplan_detail_tag' => 'condition',
                    'dialplan_detail_type' => 'destination_number',
                    'dialplan_detail_data' => '^' . $dialplanNumber . '$',
                    'dialplan_detail_break' => null,
                    'dialplan_detail_inline' => null,
                    'dialplan_detail_group' => $detailGroup,
                    'dialplan_detail_order' => $detailOrder,
                ];

                foreach ($group['conditions'] as $condition) {
                    if (empty($condition['variable']) || empty($condition['value_start'])) {
                        continue;
                    }

                    $conditionVar = $condition['variable'];
                    $conditionValue = $condition['value_start'];

                    if ($conditionVar === 'time-of-day') {
                        $conditionVar = 'minute-of-day';
                        $conditionValue = $this->timeToMinutes($condition['value_start']);

                        if (!empty($condition['value_stop'])) {
                            $conditionValue .= '-' . $this->timeToMinutes($condition['value_stop']);
                        }
                    } else {
                        if (!empty($condition['value_stop'])) {
                            $rangeIndicator = ($conditionVar === 'date-time') ? '~' : '-';
                            $conditionValue .= $rangeIndicator . $condition['value_stop'];
                        }
                    }

                    $detailOrder += 10;
                    $details[] = [
                        'dialplan_detail_tag' => 'condition',
                        'dialplan_detail_type' => $conditionVar,
                        'dialplan_detail_data' => $conditionValue,
                        'dialplan_detail_break' => 'never',
                        'dialplan_detail_inline' => null,
                        'dialplan_detail_group' => $detailGroup,
                        'dialplan_detail_order' => $detailOrder,
                    ];
                }

                if ($isPreset && $presetName) {
                    $detailOrder += 10;
                    $details[] = [
                        'dialplan_detail_tag' => 'action',
                        'dialplan_detail_type' => 'set',
                        'dialplan_detail_data' => 'preset=' . $presetName,
                        'dialplan_detail_break' => null,
                        'dialplan_detail_inline' => 'true',
                        'dialplan_detail_group' => $detailGroup,
                        'dialplan_detail_order' => $detailOrder,
                    ];
                }

                if (!empty($group['action'])) {
                    [$actionApp, $actionData] = $this->parseAction($group['action']);

                    $detailOrder += 10;
                    $details[] = [
                        'dialplan_detail_tag' => 'action',
                        'dialplan_detail_type' => $actionApp,
                        'dialplan_detail_data' => $actionData ?? '',
                        'dialplan_detail_break' => null,
                        'dialplan_detail_inline' => null,
                        'dialplan_detail_group' => $detailGroup,
                        'dialplan_detail_order' => $detailOrder,
                    ];
                }
            }
        }

        if (!empty($antiAction)) {
            [$antiActionApp, $antiActionData] = $this->parseAction($antiAction);

            $detailGroup = 999;
            $detailOrder = 0;

            $detailOrder += 10;
            $details[] = [
                'dialplan_detail_tag' => 'condition',
                'dialplan_detail_type' => 'destination_number',
                'dialplan_detail_data' => '^' . $dialplanNumber . '$',
                'dialplan_detail_break' => null,
                'dialplan_detail_inline' => null,
                'dialplan_detail_group' => $detailGroup,
                'dialplan_detail_order' => $detailOrder,
            ];

            $detailOrder += 10;
            $details[] = [
                'dialplan_detail_tag' => 'action',
                'dialplan_detail_type' => $antiActionApp,
                'dialplan_detail_data' => $antiActionData ?? '',
                'dialplan_detail_break' => null,
                'dialplan_detail_inline' => null,
                'dialplan_detail_group' => $detailGroup,
                'dialplan_detail_order' => $detailOrder,
            ];
        }

        return $details;
    }

    protected function parseAction(string $action): array
    {
        if (strpos($action, ':') !== false) {
            $parts = explode(':', $action, 2);
            return [$parts[0], $parts[1]];
        }
        return [$action, null];
    }

    protected function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int)$hours * 60) + (int)$minutes;
    }

    protected function minutesToTime(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function parseDetails(Dialplan $dialplan): array
    {
        $customGroups = [];
        $antiAction = null;
        $availablePresets = $this->getAvailablePresets();
        $currentPresetName = null;

        foreach ($dialplan->dialplanDetails as $detail) {
            $group = $detail->dialplan_detail_group;

            if ($group == 999) {
                if ($detail->dialplan_detail_tag === 'action') {
                    $antiAction = $detail->dialplan_detail_type .
                        ($detail->dialplan_detail_data ? ':' . $detail->dialplan_detail_data : '');
                }
                continue;
            }

            if (
                $detail->dialplan_detail_tag === 'action' &&
                $detail->dialplan_detail_type === 'set' &&
                strpos($detail->dialplan_detail_data, 'preset=') === 0
            ) {

                $presetName = str_replace('preset=', '', $detail->dialplan_detail_data);

                if (!isset($customGroups[$group])) {
                    $customGroups[$group] = [
                        'conditions' => [],
                        'action' => null,
                        'is_preset' => false,
                        'preset_name' => null,
                    ];
                }
                $customGroups[$group]['is_preset'] = true;
                $customGroups[$group]['preset_name'] = $presetName;
                continue;
            }

            if ($detail->dialplan_detail_type === 'destination_number') {
                continue;
            }

            if (!isset($customGroups[$group])) {
                $customGroups[$group] = [
                    'conditions' => [],
                    'action' => null,
                    'is_preset' => false,
                    'preset_name' => null,
                ];
            }

            if ($detail->dialplan_detail_tag === 'condition') {
                $conditionVar = $detail->dialplan_detail_type;
                $conditionValue = $detail->dialplan_detail_data;

                if ($conditionVar === 'minute-of-day') {
                    $conditionVar = 'time-of-day';
                    $parts = explode('-', $conditionValue);
                    $valueStart = $this->minutesToTime((int)$parts[0]);
                    $valueStop = isset($parts[1]) ? $this->minutesToTime((int)$parts[1]) : null;
                } else {
                    $rangeIndicator = ($conditionVar === 'date-time') ? '~' : '-';
                    $parts = explode($rangeIndicator, $conditionValue);
                    $valueStart = $parts[0];
                    $valueStop = $parts[1] ?? null;
                }

                $customGroups[$group]['conditions'][] = [
                    'variable' => $conditionVar,
                    'value_start' => $valueStart,
                    'value_stop' => $valueStop,
                ];
            } else if ($detail->dialplan_detail_tag === 'action') {
                $customGroups[$group]['action'] = $detail->dialplan_detail_type .
                    ($detail->dialplan_detail_data ? ':' . $detail->dialplan_detail_data : '');
            }
        }

        return [
            'custom_groups' => $customGroups,
            'anti_action' => $antiAction,
        ];
    }

    public function getTimeVariables(): array
    {
        return [
            'year' => 'Year',
            'mon' => 'Month',
            'mday' => 'Day of Month',
            'wday' => 'Day of Week',
            'week' => 'Week of Year',
            'mweek' => 'Week of Month',
            'hour' => 'Hour of Day',
            'time-of-day' => 'Time of Day',
            'date-time' => 'Date and Time',
        ];
    }
}
