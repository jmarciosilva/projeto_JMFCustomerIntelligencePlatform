<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerJourneyController extends Controller
{
    public function __invoke(Request $request, int $contactId): JsonResponse
    {
        $contact = Contact::findOrFail($contactId);

        $journey = Event::where('application_id', $request->user()->application->id)
            ->where('contact_id', $contactId)
            ->orderBy('occurred_at', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'event_id' => $event->id,
                    'event_name' => $event->event_name,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'product_id' => $event->properties?->get('product_id'),
                    'seller_id' => $event->properties?->get('seller_id'),
                    'value' => $event->properties?->get('total_value') ?? $event->properties?->get('price'),
                    'context' => [
                        'page_url' => $event->context?->get('page_url'),
                        'referrer' => $event->context?->get('referrer'),
                        'utm_source' => $event->context?->get('utm_source'),
                        'utm_medium' => $event->context?->get('utm_medium'),
                        'utm_campaign' => $event->context?->get('utm_campaign'),
                    ],
                ];
            });

        $journeyStages = $this->categorizeJourney($journey);

        return response()->json([
            'contact_id' => $contactId,
            'contact_email' => $contact->email,
            'contact_name' => $contact->name,
            'events' => $journey,
            'journey_stages' => $journeyStages,
            'total_events' => $journey->count(),
            'first_event' => $journey->first()?->toArray(),
            'last_event' => $journey->last()?->toArray(),
        ]);
    }

    private function categorizeJourney($journey): array
    {
        $eventNames = $journey->pluck('event_name')->toArray();
        $stages = [];

        if (in_array('product.viewed', $eventNames)) {
            $stages[] = 'product_discovery';
        }

        if (in_array('product.favorited', $eventNames)) {
            $stages[] = 'product_interest';
        }

        if (in_array('cart.item_added', $eventNames)) {
            $stages[] = 'cart_addition';
        }

        if (in_array('cart.viewed', $eventNames)) {
            $stages[] = 'cart_review';
        }

        if (in_array('checkout.started', $eventNames)) {
            $stages[] = 'checkout_initiated';
        }

        if (in_array('purchase.completed', $eventNames)) {
            $stages[] = 'purchase_completed';
        }

        if (in_array('review.submitted', $eventNames)) {
            $stages[] = 'review_given';
        }

        if (in_array('cart.abandoned', $eventNames) && !in_array('purchase.completed', $eventNames)) {
            $stages[] = 'cart_abandoned';
        }

        return $stages;
    }
}
