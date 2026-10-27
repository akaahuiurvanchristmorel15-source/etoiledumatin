<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Les cases à cocher non cochées ne sont pas envoyées : on les normalise en booléens.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pdf_available' => $this->boolean('is_pdf_available'),
            'is_physical_available' => $this->boolean('is_physical_available'),
            'is_audio_available' => $this->boolean('is_audio_available'),
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'audio_plan_prices' => collect(config('audio.plans'))
                ->mapWithKeys(fn (string $label, int $days) => [$days => (int) $this->input('audio_plan_prices.'.$days)])
                ->all(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:50'],
            'pages_count' => ['nullable', 'integer', 'min:1'],
            'published_year' => ['nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'weight_grams' => ['required', 'integer', 'min:50'],
            'price_pdf' => ['required', 'integer', 'min:0'],
            'price_physical' => ['required', 'integer', 'min:0'],
            'audio_plan_prices' => ['array'],
            'audio_plan_prices.*' => ['integer', 'min:0', 'max:10000000'],
            'stock_physical' => ['required', 'integer', 'min:0'],
            'audio_duration' => ['nullable', 'string', 'max:50'],
            'is_pdf_available' => ['boolean'],
            'is_physical_available' => ['boolean'],
            'is_audio_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'audio_sample_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'catégorie',
            'title' => 'titre',
            'author' => 'auteur',
            'language' => 'langue',
            'description' => 'description',
            'weight_grams' => 'poids',
            'price_pdf' => 'prix PDF',
            'price_physical' => 'prix papier',
            'audio_plan_prices.*' => 'prix d\'abonnement',
            'stock_physical' => 'stock',
            'cover_file' => 'couverture',
            'pdf_file' => 'fichier PDF',
            'audio_sample_file' => 'extrait audio',
        ];
    }
}
