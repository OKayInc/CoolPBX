@extends('layouts.app')

@section('content')
<livewire:fax-form :fax="$fax ?? null" />
@endsection

@push("scripts")
<script>
	function toggle_advanced(advanced_id)
    {
        const el = document.getElementById(advanced_id);

        if(!el)
        {
            return;
        }

        // Toggle visibility
        if (el.style.display === 'none' || getComputedStyle(el).display === 'none')
        {
            el.style.display = '';

            const top = el.getBoundingClientRect().top + window.scrollY - 80;

            window.scrollTo({ top, behavior: 'smooth' });
        }
        else
        {
            el.style.display = 'none';
        }
	}

document.addEventListener('DOMContentLoaded', function()
{
    const btn_toogle_advanced = document.getElementById('btn_toogle_advanced');
    const btn_add_sender = document.getElementById('btn_add_sender');

    if(btn_toogle_advanced)
    {
        btn_toogle_advanced.addEventListener('click', function()
        {
            toggle_advanced("advanced_email_connection");
        });
    }
});
</script>
@endpush
