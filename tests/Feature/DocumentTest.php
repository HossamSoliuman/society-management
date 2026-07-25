<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Society;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create([
        'name' => 'Green Meadows Society',
        'prefix' => 'GMS',
        'status' => 'active',
    ]);
    linkSocietyAdmin($this->user, $this->society);
});

it('loads the document management list', function () {
    DocumentCategory::factory()->create(['society_id' => $this->society->id]);
    Document::factory()->count(4)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.documents.index'))
        ->assertOk()
        ->assertSee('Document Management')
        ->assertSee('Total Documents');
});

it('loads the upload form', function () {
    $this->actingAs($this->user)->get(route('society.documents.create'))
        ->assertOk()
        ->assertSee('Document Details');
});

it('loads the categories list', function () {
    DocumentCategory::factory()->count(3)->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)->get(route('society.documents.categories'))
        ->assertOk()
        ->assertSee('Document Categories');
});

it('stores a document and redirects', function () {
    $category = DocumentCategory::factory()->create(['society_id' => $this->society->id]);

    $this->actingAs($this->user)
        ->post(route('society.documents.store'), [
            'name' => 'Society Registration Certificate',
            'document_category_id' => $category->id,
            'type' => 'PDF',
            'description' => 'Registration certificate issued by registrar',
            'confidentiality' => 'general',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('documents', [
        'name' => 'Society Registration Certificate',
        'society_id' => $this->society->id,
    ]);
});
