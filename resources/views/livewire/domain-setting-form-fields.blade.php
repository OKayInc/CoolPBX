<form wire:submit.prevent="save" id="domain-setting-form">
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle me-2"></i>Basic Information
            </h5>
        </div>

        <div class="col-md-6 mb-3">
            <label for="domain_setting_category" class="form-label">
                Category <span class="text-danger">*</span>
            </label>
            @if($canEditCategory)
                <input type="text" 
                       class="form-control @error('domain_setting_category') is-invalid @enderror" 
                       id="domain_setting_category"
                       wire:model.blur="domain_setting_category"
                       placeholder="Enter category"
                       style="text-transform: lowercase;">
            @else
                <select class="form-select @error('domain_setting_category') is-invalid @enderror" 
                        id="domain_setting_category"
                        wire:model.change="domain_setting_category">
                    <option value="">Select Category</option>
                    @foreach($allowedCategories as $category)
                        <option value="{{ $category }}">
                            {{ ucwords(str_replace('_', ' ', $category)) }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('domain_setting_category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                The main category for this setting (e.g., domain, theme, voicemail)
            </div>
        </div>

        <!-- Subcategory -->
        <div class="col-md-6 mb-3">
            <label for="domain_setting_subcategory" class="form-label">
                Subcategory <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('domain_setting_subcategory') is-invalid @enderror" 
                   id="domain_setting_subcategory"
                   wire:model.blur="domain_setting_subcategory"
                   placeholder="Enter subcategory"
                   style="text-transform: lowercase;">
            @error('domain_setting_subcategory')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                The subcategory for more specific organization
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="domain_setting_name" class="form-label">
                Type <span class="text-danger">*</span>
            </label>
            <select class="form-select @error('domain_setting_name') is-invalid @enderror" 
                    id="domain_setting_name"
                    wire:model.change="domain_setting_name">
                <option value="">Select Type</option>
                @foreach($settingTypes as $type)
                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            @error('domain_setting_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                The data type for this setting value
            </div>
        </div>

        <!-- Enabled -->
        <div class="col-md-6 mb-3">
            <label for="domain_setting_enabled" class="form-label">
                Enabled <span class="text-danger">*</span>
            </label>
            <select class="form-select @error('domain_setting_enabled') is-invalid @enderror" 
                    id="domain_setting_enabled"
                    wire:model="domain_setting_enabled">
                <option value="true">Yes - Setting is active</option>
                <option value="false">No - Setting is inactive</option>
            </select>
            @error('domain_setting_enabled')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                Disabled settings are not applied to the domain
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-input-cursor-text me-2"></i>Setting Value
            </h5>
        </div>

        <div class="col-12 mb-3">
            <label for="domain_setting_value" class="form-label">Value</label>
            
            @php
                $fieldType = $this->getValueFieldType();
            @endphp

            @if($fieldType === 'menu_select')
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="">Select Menu</option>
                    @foreach($availableMenus as $menu)
                        <option value="{{ $menu['menu_uuid'] }}">
                            {{ $menu['menu_language'] }} - {{ $menu['menu_name'] }}
                        </option>
                    @endforeach
                </select>

            @elseif($fieldType === 'theme_select')
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="">Select Theme</option>
                    @foreach($availableThemes as $theme)
                        <option value="{{ $theme['value'] }}">{{ $theme['label'] }}</option>
                    @endforeach
                </select>

            @elseif($fieldType === 'language_select')
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="">Select Language</option>
                    @foreach($availableLanguages as $lang)
                        <option value="{{ $lang }}">{{ $lang }}</option>
                    @endforeach
                </select>

            @elseif($fieldType === 'timezone_select')
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="">Select Timezone</option>
                    @foreach($availableTimezones as $tz)
                        <option value="{{ $tz['value'] }}" {{ $tz['disabled'] ? 'disabled' : '' }}>
                            {{ $tz['label'] }}
                        </option>
                    @endforeach
                </select>

            @elseif($fieldType === 'time_format_select')
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="24h">24 Hour</option>
                    <option value="12h">12 Hour</option>
                </select>

            @elseif($fieldType === 'password')
                <input type="password" 
                       class="form-control @error('domain_setting_value') is-invalid @enderror" 
                       wire:model="domain_setting_value"
                       placeholder="Enter password">

            @elseif($fieldType === 'color')
                <div class="input-group">
                    <input type="color" 
                           class="form-control form-control-color @error('domain_setting_value') is-invalid @enderror" 
                           wire:model="domain_setting_value"
                           style="max-width: 80px;">
                    <input type="text" 
                           class="form-control @error('domain_setting_value') is-invalid @enderror" 
                           wire:model="domain_setting_value"
                           placeholder="#000000">
                </div>

            @elseif(str_ends_with($fieldType, '_select'))
                @php
                    $selectOptions = match($fieldType) {
                        'fax_page_size_select' => ['letter' => 'Letter', 'legal' => 'Legal', 'a4' => 'A4'],
                        'fax_resolution_select' => ['normal' => 'Normal', 'fine' => 'Fine', 'superfine' => 'Superfine'],
                        'menu_brand_type_select', 'body_header_brand_type_select' => [
                            'image' => 'Image', 'text' => 'Text', 'image_text' => 'Image & Text', 'none' => 'None'
                        ],
                        'menu_style_select' => ['fixed' => 'Fixed', 'static' => 'Static', 'inline' => 'Inline', 'side' => 'Side'],
                        'menu_position_select' => ['top' => 'Top', 'bottom' => 'Bottom'],
                        'logo_align_select' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
                        'button_icons_select' => ['auto' => 'Auto', 'only' => 'Icons Only', 'always' => 'Always Show', 'never' => 'Never Show'],
                        'menu_side_state_select' => ['expanded' => 'Expanded', 'contracted' => 'Contracted', 'hidden' => 'Hidden'],
                        'menu_side_toggle_select' => ['hover' => 'Hover', 'click' => 'Click'],
                        'menu_side_toggle_body_width_select' => ['shrink' => 'Shrink', 'fixed' => 'Fixed'],
                        'menu_side_item_main_sub_close_select' => ['automatic' => 'Automatic', 'manual' => 'Manual'],
                        'input_toggle_style_select' => ['select' => 'Select', 'switch_round' => 'Switch (Round)', 'switch_square' => 'Switch (Square)'],
                        'username_format_select' => ['any' => 'Any Format', 'email' => 'Email Only', 'no_email' => 'No Email'],
                        'voicemail_file_select' => ['listen' => 'Listen', 'link' => 'Link', 'attach' => 'Attach'],
                        'voicemail_message_position_select' => ['before' => 'Before', 'after' => 'After', 'false' => 'False'],
                        'storage_type_select' => ['file' => 'File', 'base64' => 'Base64'],
                        'dialplan_mode_select' => ['multiple' => 'Multiple', 'single' => 'Single'],
                        'select_mode_select' => ['default' => 'Default', 'dynamic' => 'Dynamic'],
                        'aastra_time_format_select' => ['1' => '24 Hour', '0' => '12 Hour'],
                        'aastra_date_format_select' => [
                            '0' => 'WWW MMM DD', '1' => 'DD-MMM-YY', '2' => 'YYYY-MM-DD', '3' => 'DD/MM/YYYY',
                            '4' => 'DD/MM/YY', '5' => 'DD-MM-YY', '6' => 'MM/DD/YY', '7' => 'MMM DD'
                        ],
                        'boolean_select' => ['false' => 'False', 'true' => 'True'],
                        default => []
                    };
                @endphp
                <select class="form-select @error('domain_setting_value') is-invalid @enderror" 
                        wire:model="domain_setting_value">
                    <option value="">Select option</option>
                    @foreach($selectOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

            @elseif($fieldType === 'textarea_code')
                <textarea class="form-control font-monospace @error('domain_setting_value') is-invalid @enderror" 
                          wire:model="domain_setting_value" 
                          rows="12"
                          style="font-size: 13px; line-height: 1.5;"
                          placeholder="Enter custom CSS code"></textarea>

            @elseif($fieldType === 'json_textarea')
                <textarea class="form-control font-monospace @error('domain_setting_value') is-invalid @enderror" 
                          wire:model="domain_setting_value" 
                          rows="6"
                          style="font-size: 13px; line-height: 1.5;"
                          placeholder='{"key": "value"}'></textarea>

            @else
                <input type="text" 
                       class="form-control @error('domain_setting_value') is-invalid @enderror" 
                       wire:model="domain_setting_value"
                       placeholder="Enter value">
            @endif

            @error('domain_setting_value')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                The value for this setting
            </div>
        </div>

        @if($showOrderField)
            <div class="col-md-6 mb-3">
                <label for="domain_setting_order" class="form-label">
                    Order <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('domain_setting_order') is-invalid @enderror" 
                        id="domain_setting_order"
                        wire:model="domain_setting_order">
                    @for($i = 0; $i <= 999; $i += 10)
                        <option value="{{ $i }}">{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
                @error('domain_setting_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Order number for array items (lower numbers appear first)
                </div>
            </div>
        @endif
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-file-text me-2"></i>Additional Information
            </h5>
        </div>

        <div class="col-12 mb-3">
            <label for="domain_setting_description" class="form-label">Description</label>
            <textarea class="form-control @error('domain_setting_description') is-invalid @enderror" 
                      id="domain_setting_description"
                      wire:model="domain_setting_description"
                      rows="3"
                      placeholder="Enter a description for this setting (optional)"></textarea>
            @error('domain_setting_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle-fill me-1"></i>
                Optional description to help identify the purpose of this setting
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('domains_settings.index', ['domainUuid' => $domain_uuid]) }}" 
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ $isEditing ? 'Update Setting' : 'Create Setting' }}
                </button>
            </div>
        </div>
    </div>
</form>