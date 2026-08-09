<?php

use App\Domain\Affiliate\Contracts\AffiliateProviderInterface;
use App\Domain\Affiliate\Exceptions\ProviderNotConfiguredException;
use App\Domain\Affiliate\MagaluAffiliateProvider;
use App\Domain\Affiliate\ManualAffiliateProvider;
use App\Models\AffiliateProgram;

test('manual provider está sempre configurado e não busca produtos automaticamente', function () {
    $provider = new ManualAffiliateProvider;
    $program = AffiliateProgram::factory()->create(['provider' => AffiliateProgram::PROVIDER_MANUAL]);

    expect($provider->key())->toBe('manual');
    expect($provider->isConfigured())->toBeTrue();
    expect($provider->fetchProducts($program))->toBe([]);
});

test('magalu provider não está configurado e lança exceção ao buscar produtos', function () {
    $provider = new MagaluAffiliateProvider;
    $program = AffiliateProgram::factory()->create(['provider' => AffiliateProgram::PROVIDER_MAGALU]);

    expect($provider->key())->toBe('magalu');
    expect($provider->isConfigured())->toBeFalse();

    $provider->fetchProducts($program);
})->throws(ProviderNotConfiguredException::class);

test('AffiliateProviderInterface resolve para ManualAffiliateProvider por padrão', function () {
    $provider = app(AffiliateProviderInterface::class);

    expect($provider)->toBeInstanceOf(ManualAffiliateProvider::class);
});
