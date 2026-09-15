<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document_category_id' => ['required', 'integer', Rule::exists('document_categories', 'id')->where('society_id', $this->user()->society_id)],
            'type' => ['required', 'in:PDF,DOCX,JPG,XLSX'],
            'description' => ['nullable', 'string', 'max:250'],
            'related_to' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
            'confidentiality' => ['required', 'in:general,confidential,restricted'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:20480'],
        ];
    }
}
