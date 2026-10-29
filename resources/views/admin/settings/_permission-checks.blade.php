{{-- Cases à cocher des permissions (config/permissions.php) --}}
<fieldset class="stack" style="gap: .5rem; margin: 0; padding: 0; border: 0;">
    <legend class="label" style="margin-bottom: .5rem;">Permissions</legend>
    @foreach(config('permissions') as $key => $permission)
        <label class="check" for="{{ $prefix }}_{{ $key }}">
            <input type="checkbox" id="{{ $prefix }}_{{ $key }}" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $checked, true))>
            <span><span class="check-title">{{ $permission['label'] }}</span><span class="check-help">{{ $permission['description'] }}</span></span>
        </label>
    @endforeach
</fieldset>
