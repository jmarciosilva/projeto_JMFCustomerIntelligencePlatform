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

        $journey = Event::where('application_id', $request->user()->id)
            ->where('contact_id', $contactId)
            ->orderBy('occurred_at', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'event_id' => $event->id,
                    'event_name' => $event->event_name,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'product_id' => $event->properties['product_id'] ?? null,
                    'seller_id' => $event->properties['seller_id'] ?? null,
                    'value' => $event->properties['total_value'] ?? $event->properties['price'] ?? null,
                    'context' => [
                        'page_url' => $event->context['page_url'] ?? null,
                        'referrer' => $event->context['referrer'] ?? null,
                        'utm_source' => $event->context['utm_source'] ?? null,
                        'utm_medium' => $event->context['utm_medium'] ?? null,
                        'utm_campaign' => $event->context['utm_campaign'] ?? null,
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
            'first_event' => $journey->first(),
            'last_event' => $journey->last(),
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
