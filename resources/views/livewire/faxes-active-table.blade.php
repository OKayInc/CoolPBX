<tbody wire:poll.5s="refreshList">

@forelse($tasks as $t)

<tr>
    <td>{{ $t['server'] }}</td>

    <td>
        <i class="fa-regular {{ $t['enabled'] ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
    </td>


    <td>
        @if($t['status'] == 'Execute')
            <span class="badge bg-info">{{ $t['status'] }}</span>
        @elseif($t['status']=='Success')
            <span class="badge bg-success">{{ $t['status'] }}</span>
        @elseif($t['status']=='Fail')
            <span class="badge bg-danger">{{ $t['status'] }}</span>
        @else
            <span class="badge bg-secondary">{{ $t['status'] }}</span>
        @endif
    </td>

    <td>{{ $t['next_time'] }}</td>

    <td>
        @foreach($t['files'] as $f)
            {{ $f }}<br>
        @endforeach
    </td>

    <td class="small text-muted">
        {{ $t['uri'] }}
    </td>

    <td width="40">
        <button
            wire:click="delete('{{ $t['uuid'] }}')"
            wire:confirm="Cancel fax?"
            class="btn btn-xs btn-danger">
            <i class="fas fa-times"></i>
        </button>
    </td>
</tr>

@empty
<tr>
    <td colspan="5" class="text-muted">
        {{ __('No active faxes') }}
    </td>
</tr>
@endforelse

</tbody>
