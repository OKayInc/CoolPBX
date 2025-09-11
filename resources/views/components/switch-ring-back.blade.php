<div>
    <select class="form-select" name="{{ $name }}" id="{{ $name }}" {{ $attributes }}>
        <option value="">{{ __('Select ringback...') }}</option>

        @foreach ($options as $group)
            @if (!empty($group->values))
                <optgroup label="{{ $group->label }}">
                    @foreach ($group->values as $option)
                        <option value="{{ $option->id }}" @if ($selected == $option->id) selected @endif>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
    </select>
</div>
