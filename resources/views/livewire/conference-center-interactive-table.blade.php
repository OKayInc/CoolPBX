<tbody wire:poll.10s="refreshList">
    @foreach($data["body"] as $row)
        @if(!empty($row["record_path"]))
            @continue
        @endif

        @php
            $hand_raise_visibility = ($row["hand_raised"]) ? "visible" : "hidden";

            $action_mute = ($row["flag_can_speak"]) ? 'mute' : 'unmute';
            $href = 'conference_exec.php?cmd=conference&name="' . $data['head']['conference_name'] . '&data="' . $action_mute . '"&id="' . $row["id"] . '"';
        @endphp
        <tr data-href="{{ $href }}">
			<td>{{ $row["caller_id_name"] }}</td>
			<td>{{ $row["caller_id_number"] }}</td>
			<td>{{ $row["join_time_formatted"] }}</td>
			<td>{{ $row["last_talking_formatted"] }}</td>
			<td>{{ ($row["flag_has_floor"]) ? "yes" : "no" }}</td>
			<td>{{ ($row["hand_raised"]) ? "yes" : "no" }} <i class="fas fa-hand-paper" style="font-size: 14px; margin: -2px 10px -2px 15px; visibility: {{ $hand_raise_visibility }};"</i></td>
            <td>
                {!! ($row["flag_can_speak"]) ? '<i class="fas fa-microphone fa-fw" title="'.__('Speak').'"></i>' : '<i class="fas fa-microphone-slash fa-fw" title="'.__('Speak').'"></i>' !!}

                {!! ($row["flag_can_hear"]) ? '<i class="fas fa-headphones fa-fw" title="'.__('Hear').'"></i>' : '<i class="fas fa-deaf fa-fw" title="'.__('Hear').'"></i>' !!}

                @can('conference_interactive_video')
                    {!! ($row["flag_has_video"]) ? '<i class="fas fa-video fa-fw" title="'.__('Video').'" style="margin-left:10px;"></i>' : '' !!}
                @endcan
            </td>

            @can('conference_interactive_energy')
                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'down',
                            'name' => $data['head']['conference_name'],
                            'data' => 'energy',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-plus"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'energy',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-minus"></i>
                    </button>

                </td>
            @endcan

            @can('conference_interactive_volume')
                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'volume_in',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-volume-high"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'volume_in',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-volume-low"></i>
                    </button>
                </td>
            @endcan

            @can('conference_interactive_gain')
                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'volume_out',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'volume_out',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-arrow-up-short-wide"></i>
                    </button>
                </td>
            @endcan

            <td>
            @can('conference_interactive_mute')
                @if($action_mute == "mute")
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'mute',
                            'id' => $row['id'],
                        ]))"
                    >
                        <i class="fa-solid fa-microphone-slash"></i>
                    </button>
                @else
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'unmute',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-microphone"></i>
                    </button>
                @endif
            @endcan

            @can('conference_interactive_deaf')
                @if($row["flag_can_hear"])
                    <button type="button" class="btn btn-sm btn-primary" data-command="cmd=conference&name={{ $data['head']['conference_name'] }}&data=deaf&id={{ $row['id'] }}"><i class="fa-solid fa-deaf"></i></button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'deaf',
                            'id' => $row['id'],
                        ]))"
                    >
                        <i class="fa-solid fa-deaf"></i>
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-primary" data-command="cmd=conference&name={{ $data['head']['conference_name'] }}&data=undeaf&id={{ $row['id'] }}"><i class="fa-solid fa-headphones"></i></button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'undeaf',
                            'id' => $row['id'],
                        ]))"
                    >
                        <i class="fa-solid fa-headphones"></i>
                    </button>
                @endif
            @endcan

            @can('conference_interactive_kick')
                @if($row["flag_can_hear"])
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="runCommand(@js([
                            'cmd' => 'conference',
                            'direction' => 'up',
                            'name' => $data['head']['conference_name'],
                            'data' => 'kick',
                            'id' => $row['id'],
                            'uuid' => $row['uuid'],
                        ]))"
                    >
                        <i class="fa-solid fa-ban"></i>
                    </button>
                @endif
            @endcan
            </td>
        </tr>
    @endforeach
</tbody>
