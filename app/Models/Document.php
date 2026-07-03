<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'document_category_id', 'type', 'description',
        'related_to', 'tags', 'expiry_date', 'confidentiality', 'uploaded_by',
        'size', 'file_path', 'downloads',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'expiry_date' => 'date',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    /** Font Awesome icon class for the document's file type. */
    public function typeIcon(): string
    {
        return match (strtolower((string) $this->type)) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'jpg', 'jpeg', 'png' => 'fa-file-image',
            default => 'fa-file',
        };
    }
}
