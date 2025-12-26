<tbody wire:poll.10s="refreshList">
    @foreach($data["body"] as $x)


        <tr>

			<td>{{ $x["caller_id_name"] }}</td>
			<td>{{ $x["caller_id_name"] }}</td>
			<td>{{ $x["join_time_formatted"] }}</td>
			<td>{{ $x["last_talking_formatted"] }}</td>
			<td>{{ ($x["flag_has_floor"]) ? "yes" : "no" }}</td>
			<td>{{ ($x["hand_raised"]) ? "visible" : "hidden" }}</td>
            @can('conference_interactive_energy')
                <td>Energy</td>
            @endcan

            @can('conference_interactive_volume')
                <td>Volume</td>
            @endcan

            @can('conference_interactive_gain')
                <td>Gain</td>
            @endcan
        </tr>
    @endforeach
</tbody>
