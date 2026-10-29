<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Sac d'erreurs propre à chaque formulaire : la page affiche un formulaire par rôle, plus celui de création.
     */
    protected function prepareForValidation(): void
    {
        $this->errorBag = $this->route('role') ? 'role'.$this->route('role')->id : 'createRole';

        $this->merge(['permissions' => array_values((array) $this->input('permissions', []))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($this->route('role'))],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(array_keys(config('permissions')))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom du rôle',
            'description' => 'description',
            'permissions.*' => 'permission',
        ];
    }
}
