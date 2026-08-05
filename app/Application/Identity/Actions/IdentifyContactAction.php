<?php

namespace App\Application\Identity\Actions;

use App\Models\Application;
use App\Models\Contact;
use App\Models\ContactConsent;
use App\Models\Visitor;

class IdentifyContactAction
{
    /**
     * @param  array<string, mixed>  $data  Payload validado (IdentifyContactRequest::validated()).
     */
    public function handle(Application $application, array $data): Contact
    {
        $contact = $this->findOrCreateContact($application->tenant_id, $data);

        $this->linkVisitor($application, $contact, $data['visitor_id']);

        $this->syncConsents($contact, $application, $data['consents'] ?? []);

        return $contact;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function findOrCreateContact(int $tenantId, array $data): Contact
    {
        $contact = null;

        if (! empty($data['external_id'])) {
            $contact = Contact::query()
                ->where('tenant_id', $tenantId)
                ->where('external_id', $data['external_id'])
                ->first();
        }

        if (! $contact && ! empty($data['email'])) {
            $contact = Contact::query()
                ->where('tenant_id', $tenantId)
                ->where('email', $data['email'])
                ->first();
        }

        if (! $contact) {
            $contact = new Contact([
                'tenant_id' => $tenantId,
                'first_identified_at' => now(),
            ]);
        }

        // Só sobrescreve campos presentes no payload — identify() é
        // frequentemente chamado de forma incremental (ex.: só email na
        // primeira visita, nome depois num cadastro), e não deve apagar
        // dados já conhecidos do contato.
        $attributes = [];

        foreach (['external_id', 'email', 'name', 'phone'] as $field) {
            if (! empty($data[$field])) {
                $attributes[$field] = $data[$field];
            }
        }

        if (! empty($data['properties'])) {
            $attributes['properties'] = array_merge($contact->properties ?? [], $data['properties']);
        }

        $attributes['last_seen_at'] = now();

        $contact->fill($attributes);
        $contact->save();

        return $contact;
    }

    private function linkVisitor(Application $application, Contact $contact, string $visitorId): void
    {
        $visitor = Visitor::query()->firstOrNew([
            'application_id' => $application->id,
            'visitor_id' => $visitorId,
        ]);

        if (! $visitor->exists) {
            $visitor->tenant_id = $application->tenant_id;
            $visitor->first_seen_at = now();
        }

        $visitor->contact_id = $contact->id;
        $visitor->last_seen_at = now();
        $visitor->save();
    }

    /**
     * @param  list<array{purpose: string, granted: bool}>  $consents
     */
    private function syncConsents(Contact $contact, Application $application, array $consents): void
    {
        foreach ($consents as $consent) {
            ContactConsent::query()->updateOrCreate(
                ['contact_id' => $contact->id, 'purpose' => $consent['purpose']],
                [
                    'source_application_id' => $application->id,
                    'granted' => $consent['granted'],
                    'granted_at' => $consent['granted'] ? now() : null,
                    'revoked_at' => $consent['granted'] ? null : now(),
                ]
            );
        }
    }
}
