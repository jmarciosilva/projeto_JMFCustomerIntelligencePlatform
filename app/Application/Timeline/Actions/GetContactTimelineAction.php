<?php

namespace App\Application\Timeline\Actions;

use App\Models\Contact;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;

class GetContactTimelineAction
{
    public function handle(Contact $contact, int $perPage = 20): LengthAwarePaginator
    {
        $visitors = $contact->visitors()->get(['id', 'application_id', 'visitor_id']);

        if ($visitors->isEmpty()) {
            return new ConcreteLengthAwarePaginator([], 0, $perPage);
        }

        return Event::query()
            ->where(function ($query) use ($visitors): void {
                foreach ($visitors as $visitor) {
                    $query->orWhere(function ($inner) use ($visitor): void {
                        $inner->where('application_id', $visitor->application_id)
                            ->where('visitor_id', $visitor->visitor_id);
                    });
                }
            })
            ->orderByDesc('occurred_at')
            ->paginate($perPage);
    }
}
