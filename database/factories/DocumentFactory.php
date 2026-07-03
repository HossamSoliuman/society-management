<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['PDF', 'DOCX', 'JPG', 'XLSX']);

        return [
            'society_id' => Society::query()->value('id'),
            'name' => $this->faker->randomElement([
                'AGM Minutes', 'Annual Audit Report', 'Society Registration Certificate',
                'Fire NOC', 'Lift License', 'Insurance Policy', 'Vendor Agreement',
                'Maintenance Circular', 'Property Tax Receipt', 'Water Bill',
            ]).' '.$this->faker->year(),
            'document_category_id' => DocumentCategory::query()->inRandomOrder()->value('id'),
            'type' => $type,
            'description' => $this->faker->optional()->sentence(),
            'related_to' => $this->faker->randomElement(['Society', 'Tower A', 'Tower B', 'Clubhouse', 'All Members']),
            'tags' => $this->faker->randomElements(['legal', 'finance', 'urgent', 'annual', 'compliance', 'archive'], 2),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years')?->format('Y-m-d'),
            'confidentiality' => $this->faker->randomElement(['general', 'general', 'confidential', 'restricted']),
            'uploaded_by' => $this->faker->name(),
            'size' => $this->faker->randomFloat(2, 0.1, 9.9).' MB',
            'file_path' => 'documents/'.$this->faker->uuid().'.'.strtolower($type),
            'downloads' => $this->faker->numberBetween(0, 250),
        ];
    }
}
