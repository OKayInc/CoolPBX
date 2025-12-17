<tbody wire:poll.1s="refreshStatus">
	<tr>
		<td>{{ $status['waiting'] }}</td>
		<td>{{ $status['trying'] }}</td>
		<td>{{ $status['answered'] }}</td>
	</tr>
</tbody>
