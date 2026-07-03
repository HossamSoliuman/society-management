<?php

namespace Database\Factories;

use App\Models\ServiceVendor;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceVendor>
 */
class ServiceVendorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = $this->faker->company();

        return [
            'society_id' => Society::query()->value('id'),
            'vendor_code' => 'VND-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => $company,
            'company' => $company,
            'category' => $this->faker->randomElement([
                'Electrical', 'Housekeeping', 'Security', 'Pest Control',
                'Maintenance', 'Waste Management', 'Plumbing',
            ]),
            'contact_person' => $this->faker->name(),
            'designation' => $this->faker->randomElement(['Manager', 'Proprietor', 'Director', 'Sales Head']),
            'gst_number' => strtoupper($this->faker->bothify('##???####?#Z#')),
            'pan_number' => strtoupper($this->faker->bothify('?????####?')),
            'phone' => $this->faker->numerify('98########'),
            'alternate_phone' => $this->faker->numerify('97########'),
            'email' => $this->faker->unique()->companyEmail(),
            'website' => $this->faker->url(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement(['Maharashtra', 'Karnataka', 'Delhi', 'Gujarat', 'Tamil Nadu']),
            'pin_code' => $this->faker->numerify('4#####'),
            'bank_name' => $this->faker->randomElement(['SBI', 'HDFC Bank', 'ICICI Bank', 'Axis Bank']),
            'account_number' => $this->faker->numerify('##############'),
            'ifsc_code' => strtoupper($this->faker->bothify('????0######')),
            'account_holder_name' => $company,
            'services_provided' => $this->faker->sentence(),
            'payment_terms' => $this->faker->randomElement(['Net 15', 'Net 30', 'Net 45', 'Advance']),
            'credit_limit' => $this->faker->randomFloat(2, 10000, 500000),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
            'approval_status' => $this->faker->randomElement(['approved', 'approved', 'pending', 'rejected']),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['approval_status' => 'pending']);
    }
}
