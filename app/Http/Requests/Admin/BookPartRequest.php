<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookPartRequest extends FormRequest
{
    /**
     * Sac d'erreurs distinct : le formulaire du livre, sur la même page, a aussi un champ « title ».
     */
    protected $errorBag = 'part';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'position' => ['required', 'integer', 'min:1', 'max:999'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'position' => 'numéro de partie',
            'title' => 'titre de la partie',
            'summary' => 'résumé',
        ];
    }
}
