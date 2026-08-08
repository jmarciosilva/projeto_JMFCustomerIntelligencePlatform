<?php

namespace App\Domain\Intelligence;

use App\Models\Contact;

class SegmentationEngine
{
    public const SEGMENT_VIP = 'vip';
    public const SEGMENT_ENGAGED = 'engaged';
    public const SEGMENT_CONVERTED = 'converted';
    public const SEGMENT_INACTIVE = 'inactive';
    public const SEGMENT_NEW = 'new';

    public function segment(Contact $contact, array $scores): string
    {
        // VIP: High customer score AND recurrent customer
        if ($scores['customer_score'] >= 80 && $this->isRecurrent($contact)) {
            return self::SEGMENT_VIP;
        }

        // Inactive: No activity in 30+ days (check this before new to prioritize)
        if ($contact->last_seen_at && $contact->last_seen_at->diffInDays(now()) >= 30) {
            return self::SEGMENT_INACTIVE;
        }

        // New: First seen in last 7 days AND active
        if ($contact->first_identified_at && $contact->first_identified_at->diffInDays(now()) <= 7) {
            // Make sure it's not inactive
            if (!$contact->last_seen_at || $contact->last_seen_at->diffInDays(now()) < 30) {
                return self::SEGMENT_NEW;
            }
        }

        // Converted: At least one purchase
        if ($this->hasPurchased($contact)) {
            return self::SEGMENT_CONVERTED;
        }

        // Engaged: High engagement but no purchase yet
        if ($scores['customer_score'] >= 50) {
            return self::SEGMENT_ENGAGED;
        }

        return self::SEGMENT_CONVERTED; // Default fallback
    }

    private function isRecurrent(Contact $contact): bool
    {
        $purchaseCount = $contact->events()
            ->where('event_name', 'purchase.completed')
            ->count();

        return $purchaseCount >= 2;
    }

    private function hasPurchased(Contact $contact): bool
    {
        return $contact->events()
            ->where('event_name', 'purchase.completed')
            ->exists();
    }
}
