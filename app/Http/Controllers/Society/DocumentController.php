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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    private const DISK = 'local';

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $documents = Document::query()
            ->with('category')
            ->forSociety($society)
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
            'stats' => $this->documentStats($society),
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

        $file = $request->file('file');
        $path = $file->store("documents/{$society->id}", self::DISK);
        $extension = strtoupper($file->getClientOriginalExtension() ?: $data['type']);

        $document = Document::create([
            'society_id' => $society->id,
            'name' => $data['name'],
            'document_category_id' => $data['document_category_id'],
            'type' => in_array($extension, self::DOCUMENT_TYPES, true) ? $extension : $data['type'],
            'description' => $data['description'] ?? null,
            'related_to' => $data['related_to'] ?? null,
            'tags' => $tags,
            'expiry_date' => $data['expiry_date'] ?? null,
            'confidentiality' => $data['confidentiality'],
            'uploaded_by' => $request->user()->name,
            'size' => $this->humanSize($file->getSize()),
            'file_path' => $path,
            'downloads' => 0,
        ]);

        return redirect()->route('society.documents.index')
            ->with('success', "Document \"{$document->name}\" uploaded successfully.");
    }

    public function download(Document $document): StreamedResponse|RedirectResponse
    {
        return $this->serve($document, inline: false);
    }

    public function preview(Document $document): StreamedResponse|RedirectResponse
    {
        return $this->serve($document, inline: true);
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->file_path && Storage::disk(self::DISK)->exists($document->file_path)) {
            Storage::disk(self::DISK)->delete($document->file_path);
        }
        $document->delete();

        return redirect()->route('society.documents.index')
            ->with('success', "Document \"{$document->name}\" deleted.");
    }

    public function categories(): View
    {
        $society = $this->currentSociety();

        $categories = DocumentCategory::query()
            ->forSociety($society)
            ->withCount('documents')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('society.documents.categories', [
            'categories' => $categories,
            'stats' => $this->categoryStats($society),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $this->validateCategory($request, $society);

        $category = DocumentCategory::create($data + ['society_id' => $society->id]);

        return redirect()->route('society.documents.categories')
            ->with('success', "Category \"{$category->name}\" created.");
    }

    public function updateCategory(Request $request, DocumentCategory $category): RedirectResponse
    {
        $society = $this->currentSociety();
        $category->update($this->validateCategory($request, $society, $category));

        return redirect()->route('society.documents.categories')
            ->with('success', "Category \"{$category->name}\" updated.");
    }

    public function destroyCategory(DocumentCategory $category): RedirectResponse
    {
        if ($category->documents()->exists()) {
            return back()->with('error', "\"{$category->name}\" still has documents; move them first.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('society.documents.categories')->with('success', "Category \"{$name}\" deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, Society $society, ?DocumentCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('document_categories', 'name')->where('society_id', $society->id)->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /**
     * Stream the stored file (route binding already enforces society ownership).
     */
    private function serve(Document $document, bool $inline): StreamedResponse|RedirectResponse
    {
        if (! $document->file_path || ! Storage::disk(self::DISK)->exists($document->file_path)) {
            return back()->with('error', 'The file for this document is not available on the server.');
        }

        $document->increment('downloads');
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION) ?: $document->type);
        $filename = str($document->name)->slug()->toString().'.'.$extension;

        return $inline
            ? Storage::disk(self::DISK)->response($document->file_path, $filename)
            : Storage::disk(self::DISK)->download($document->file_path, $filename);
    }

    /**
     * @return Collection<int, DocumentCategory>
     */
    private function categoryOptions(?Society $society): Collection
    {
        return DocumentCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, string>
     */
    private function uploaders(?Society $society): Collection
    {
        return Document::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->whereNotNull('uploaded_by')
            ->distinct()
            ->orderBy('uploaded_by')
            ->pluck('uploaded_by');
    }

    /**
     * @return array<string, string>
     */
    private function documentStats(Society $society): array
    {
        $base = Document::query()->forSociety($society);
        $bytes = 0;
        foreach ((clone $base)->whereNotNull('file_path')->pluck('file_path') as $path) {
            $bytes += Storage::disk(self::DISK)->exists($path) ? (int) Storage::disk(self::DISK)->size($path) : 0;
        }

        return [
            'total' => number_format((clone $base)->count()),
            'categories' => number_format(DocumentCategory::query()->forSociety($society)->count()),
            'total_size' => $this->humanSize($bytes),
            'downloads' => number_format((int) (clone $base)->sum('downloads')),
            'expiring_soon' => number_format((clone $base)->whereNotNull('expiry_date')->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->count()),
        ];
    }

    /**
     * @return array<string, string|int>
     */
    private function categoryStats(Society $society): array
    {
        $base = DocumentCategory::query()->forSociety($society);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'documents' => Document::query()->forSociety($society)->count(),
        ];
    }

    private function humanSize(int|false|null $bytes): string
    {
        $bytes = (int) $bytes;

        return match (true) {
            $bytes >= 1073741824 => number_format($bytes / 1073741824, 2).' GB',
            $bytes >= 1048576 => number_format($bytes / 1048576, 2).' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 1).' KB',
            default => $bytes.' B',
        };
    }
}
