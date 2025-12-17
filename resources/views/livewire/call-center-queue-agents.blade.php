<tbody wire:poll.1s="refreshAgents">
    @foreach($agents as $agent)
        <tr>
            <td><a href="{{ route('call_center_agent.edit', $agent['uuid']) }}">{{ $agent['name'] }}</a></td>
            <td>{{ $agent['extension'] }}</td>
            <td>{{ $agent['status'] }}</td>
            <td>{{ $agent['state'] }}</td>
            <td>{{ $agent['status_change'] }}</td>
            <td>{{ $agent['missed'] }}</td>
            <td>{{ $agent['answered'] }}</td>
            <td>{{ $agent['tier_state'] }}</td>
            <td>{{ $agent['tier_level'] }}</td>
            <td>{{ $agent['tier_position'] }}</td>
        </tr>
    @endforeach
</tbody>
