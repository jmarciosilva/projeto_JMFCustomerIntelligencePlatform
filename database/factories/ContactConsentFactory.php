<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\ConsentPurpose;
use App\Models\Contact;
use App\Models\ContactConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactConsent>
 */
class ContactConsentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'source_application_id' => null,
            'purpose' => ConsentPurpose::Marketing->value,
            'granted' => true,
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }
}
