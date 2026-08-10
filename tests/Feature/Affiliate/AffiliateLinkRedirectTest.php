<?php

use App\Domain\Affiliate\AffiliateLinkGenerator;
use App\Models\AffiliateClick;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Campaign;
use App\Models\Tenant;

describe('Affiliate Link Redirect', function () {
    beforeEach(function () {
        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);

        $program = AffiliateProgram::factory()->create([
            'application_id' => $this->application->id,
        ]);

        $this->product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $program->id,
            'affiliate_url' => 'https://afiliados.magalu.com.br/p/iphone-15',
        ]);

        $this->campaign = Campaign::factory()->create([
            'application_id' => $this->application->id,
        ]);
    });

    it('gera slug único para link', function () {
        $generator = app(AffiliateLinkGenerator::class);

        $slug1 = $generator->generateSlug('iPhone 15 Review');
        $slug2 = $generator->generateSlug('iPhone 15 Review');

        expect($slug1)->toBe('iphone-15-review');
        expect($slug2)->toContain('iphone-15-review');
        expect($slug1)->not->toBe($slug2);
    });

    it('constrói redirect URL com UTMs', function () {
        $generator = app(AffiliateLinkGenerator::class);

        $url = $generator->buildRedirectUrl(
            'https://example.com/product',
            'instagram',
            'story',
            'campaign-123'
        );

        expect($url)->toContain('utm_source=instagram');
        expect($url)->toContain('utm_medium=story');
        expect($url)->toContain('utm_campaign=campaign-123');
    });

    it('registra click e redireciona', function () {
        $link = AffiliateLink::factory()->create([
            'campaign_id' => $this->campaign->id,
            'affiliate_product_id' => $this->product->id,
            'affiliate_url' => 'https://afiliados.magalu.com.br/p/iphone-15',
            'slug' => 'iphone-review',
            'status' => AffiliateLink::STATUS_ACTIVE,
        ]);

        $response = $this->get('/go/iphone-review');

        expect($response->status())->toBe(302);
        expect($response->headers->get('location'))->toContain('afiliados.magalu.com.br');
    });

    it('registra dados do click', function () {
        $link = AffiliateLink::factory()->create([
            'campaign_id' => $this->campaign->id,
            'affiliate_product_id' => $this->product->id,
            'slug' => 'iphone-review',
            'status' => AffiliateLink::STATUS_ACTIVE,
        ]);

        $this->get('/go/iphone-review?utm_source=instagram&utm_medium=story');

        $click = AffiliateClick::where('affiliate_link_id', $link->id)->first();

        expect($click)->not->toBeNull();
        expect($click->source)->toBe('instagram');
        expect($click->medium)->toBe('story');
        expect($click->user_agent)->not->toBeNull();
    });

    it('incrementa contador de clicks', function () {
        $link = AffiliateLink::factory()->create([
            'campaign_id' => $this->campaign->id,
            'affiliate_product_id' => $this->product->id,
            'slug' => 'iphone-review',
            'clicks' => 0,
            'status' => AffiliateLink::STATUS_ACTIVE,
        ]);

        expect($link->clicks)->toBe(0);

        $this->get('/go/iphone-review');
        $link->refresh();

        expect($link->clicks)->toBe(1);

        $this->get('/go/iphone-review');
        $link->refresh();

        expect($link->clicks)->toBe(2);
    });

    it('retorna 404 para link inativo', function () {
        AffiliateLink::factory()->create([
            'campaign_id' => $this->campaign->id,
            'affiliate_product_id' => $this->product->id,
            'slug' => 'inactive-link',
            'status' => AffiliateLink::STATUS_ARCHIVED,
        ]);

        $response = $this->get('/go/inactive-link');

        expect($response->status())->toBe(404);
    });

    it('retorna 404 para slug inexistente', function () {
        $response = $this->get('/go/nonexistent-slug');

        expect($response->status())->toBe(404);
    });
});
