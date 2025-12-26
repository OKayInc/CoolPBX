<tbody wire:poll.10s="refreshList">
    @foreach($conferenceRooms as $conferenceRoom)
        <tr>
            <td>
                @can('conference_interactive_view')
                <a href="{{ route('conference_centers.interactive', $conferenceRoom['conference_room_uuid']) }}">
                    {{ $conferenceRoom['conference_room_name'] }}
                </a>
                @else
                    {{ $conferenceRoom['conference_room_name'] }}
                @endcan
            </td>
            <td>{{ $conferenceRoom['conference_center_extension'] }}</td>
            <td>{{ $conferenceRoom['participant_pin'] }}</td>
            <td>{{ $conferenceRoom['memberCount'] }}</td>
        </tr>
    @endforeach
</tbody>
