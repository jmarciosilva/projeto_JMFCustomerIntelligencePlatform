<?php

namespace JmfSystem\CustomerIntelligence\Support;

use Illuminate\Http\Request;

/**
 * Monta o `context` do evento (page_url, referrer, UTMs) a partir da
 * requisição HTTP atual da aplicação cliente, no formato descrito em
 * EVENT_CATALOG.md.
 */
class RequestContextResolver
{
    public function __construct(private readonly Request $request) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        return array_filter([
            'page_url' => '/'.ltrim($this->request->path(), '/'),
            'referrer' => $this->request->headers->get('referer'),
            'utm_source' => $this->request->query('utm_source'),
            'utm_medium' => $this->request->query('utm_medium'),
            'utm_campaign' => $this->request->query('utm_campaign'),
        ], fn ($value) => $value !== null);
    }
}
