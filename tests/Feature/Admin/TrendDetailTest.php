<?php

use App\Livewire\Admin\Trends\TrendShow;
use App\Models\Trend;
use App\Models\TrendSnapshot;
use App\Models\User;
use Livewire\Livewire;

test('super admin visualiza o detalhe da tendência com histórico', function () {
    $admin = superAdmin();
    $trend = Trend::factory()->create(['term' => 'cafeteira']);
    TrendSnapshot::factory()->for($trend)->create(['collected_at' => now()->subDays(2), 'mentions' => 10]);

    $this->actingAs($admin)
        ->get(route('admin.trends.show', $trend))
        ->assertOk()
        ->assertSeeLivewire(TrendShow::class)
        ->assertSee('cafeteira');
});

test('registrar observação manual cria snapshot e aparece na listagem', function () {
    $admin = superAdmin();
    $trend = Trend::factory()->create();

    Livewire::actingAs($admin)
        ->test(TrendShow::class, ['trend' => $trend])
        ->set('mentions', '25')
        ->set('score', '80')
        ->set('notes', 'Observação de teste')
        ->call('registerManualSnapshot')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('trend_snapshots', [
        'trend_id' => $trend->id,
        'source' => TrendSnapshot::SOURCE_MANUAL,
        'mentions' => 25,
    ]);
});

test('filtro de período limita os snapshots exibidos', function () {
    $admin = superAdmin();
    $trend = Trend::factory()->create();
    TrendSnapshot::factory()->for($trend)->create(['collected_at' => now()->subDays(5), 'mentions' => 1]);
    TrendSnapshot::factory()->for($trend)->create(['collected_at' => now()->subDays(100), 'mentions' => 2]);

    $component = Livewire::actingAs($admin)->test(TrendShow::class, ['trend' => $trend]);

    $component->set('period', 7);
    expect($component->viewData('snapshots'))->toHaveCount(1);

    $component->set('period', 365);
    expect($component->viewData('snapshots'))->toHaveCount(2);
});

test('usuário sem permissão não acessa o detalhe da tendência', function () {
    $user = User::factory()->create();
    $trend = Trend::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.trends.show', $trend))
        ->assertForbidden();
});
