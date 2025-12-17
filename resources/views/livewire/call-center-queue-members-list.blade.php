<tbody wire:poll.1s="refreshMembers">
    @foreach($members as $member)
        <tr>
            <td>{{ $member['joined_length'] }}</td>
            <td>{{ $member['caller_name'] }}</td>
            <td>{{ $member['caller_number'] }}</td>
            <td>{{ $member['state'] }}</td>
            <td>{{ $member['serving_agent_name'] }}</td>
        </tr>
    @endforeach
</tbody>
