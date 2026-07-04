<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /** Document file types offered across the upload form and filters. */
    private const DOCUMENT_TYPES = ['PDF', 'DOCX', 'JPG', 'XLSX'];

    /** Confidentiality levels offered on the upload form. */
    private const CONFIDENTIALITY = [
        'general' => 'General',
        'confidential' => 'Confidential',
        'restricted' => 'Restricted',
    ];

    /** Related-to options offered on the upload form. */
    private const RELATED_TO = ['Society', 'Tower A', 'Tower B', 'Tower C', 'Clubhouse', 'All Members'];

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $documents = Document::query()
            ->with('category')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('category'), fn ($q) => $q->where('document_category_id', $request->integer('category')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('uploaded_by'), fn ($q) => $q->where('uploaded_by', $request->string('uploaded_by')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('related_to', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('society.documents.index', [
            'documents' => $documents,
            'stats' => $this->documentStats(),
            'categories' => $this->categoryOptions($society),
            'documentTypes' => self::DOCUMENT_TYPES,
            'uploaders' => $this->uploaders($society),
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        return view('society.documents.upload', [
            'categories' => $this->categoryOptions($society),
            'documentTypes' => self::DOCUMENT_TYPES,
            'confidentialityLevels' => self::CONFIDENTIALITY,
            'relatedOptions' => self::RELATED_TO,
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $tags = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        $document = Document::create([
            'society_id' => $society?->id,
            'name' => $data['name'],
            'document_category_id' => $data['document_category_id'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'related_to' => $data['related_to'] ?? null,
            'tags' => $tags,
            'expiry_date' => $data['expiry_date'] ?? null,
            'confidentiality' => $data['confidentiality'],
            'uploaded_by' => 'Society Admin',
            'size' => $data['size'] ?? '1.24 MB',
            'file_path' => 'documents/'.Str::uuid().'.'.strtolower($data['type']),
            'downloads' => 0,
        ]);

        return redirect()->route('society.documents.index')
            ->with('success', "Document \"{$document->name}\" uploaded successfully.");
    }

    public function categories(): View
    {
        $society = $this->currentSociety();

        $categories = DocumentCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->withCount('documents')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('society.documents.categories', [
            'categories' => $categories,
            'stats' => $this->categoryStats($society),
        ]);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();

        return redirect()->route('society.documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /* -------------------------------------------------------------------------
     |  Shared option data
     |------------------------------------------------------------------------- */

    /**
     * @return Collection<int, DocumentCategory>
     */
    private function categoryOptions(?Society $society)
    {
        return DocumentCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();
    }

    /**
     * Distinct uploader names for the "Uploaded By" filter.
     *
     * @return Collection<int, string>
     */
    private function uploaders(?Society $society)
    {
        return Document::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->whereNotNull('uploaded_by')
            ->distinct()
            ->orderBy('uploaded_by')
            ->pluck('uploaded_by');
    }

    /* -------------------------------------------------------------------------
     |  Demo figures (stat cards) matching the PNGs
     |------------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    private function documentStats(): array
    {
        return [
            'total' => '243',
            'categories' => '18',
            'total_size' => '2.45 GB',
            'downloads' => '126',
            'expiring_soon' => '7',
        ];
    }

    /**
     * @return array<string, string|int>
     */
    private function categoryStats(?Society $society): array
    {
        $base = DocumentCategory::query()->when($society, fn ($q) => $q->where('society_id', $society->id));

        return [
            'total' => '18',
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'documents' => '243',
        ];
    }
}
