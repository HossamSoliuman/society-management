<?php

namespace Database\Factories;

use App\Models\DocumentCategory;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentCategory>
 */
class DocumentCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Legal Documents', 'Financial Records', 'Meeting Minutes', 'Contracts',
            'Compliance', 'Insurance', 'Maintenance Records', 'Member Documents',
            'Vendor Agreements', 'Circulars & Notices', 'Audit Reports', 'Bylaws',
        ]);

        return [
            'society_id' => Society::query()->value('id'),
            'name' => $name,
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->randomElement(['fa-file-lines', 'fa-scale-balanced', 'fa-file-invoice', 'fa-folder', 'fa-shield-halved']),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
            'color' => $this->faker->randomElement(['blue', 'green', 'red', 'orange', 'purple']),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
