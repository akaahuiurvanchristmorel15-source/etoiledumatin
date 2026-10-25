<?php

namespace App\Http\Requests\Admin;

use App\Enums\NewsType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsRequest extends FormRequest
{
    /**
     * Les cases à cocher non cochées ne sont pas envoyées : on les normalise en booléens.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
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
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(NewsType::class)],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'book_id' => ['nullable', 'exists:books,id'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['boolean'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'remove_cover' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'type' => 'type',
            'excerpt' => 'extrait',
            'content' => 'contenu',
            'event_date' => 'date de l\'événement',
            'location' => 'lieu',
            'book_id' => 'livre lié',
            'published_at' => 'date de publication',
            'cover_file' => 'image de couverture',
        ];
    }
}
