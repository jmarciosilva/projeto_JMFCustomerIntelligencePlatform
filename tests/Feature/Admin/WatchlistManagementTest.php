<?php

use App\Domain\Trends\WatchlistTrendSynchronizer;
use App\Livewire\Admin\Trends\WatchlistForm;
use App\Livewire\Admin\Trends\WatchlistIndex;
use App\Livewire\Admin\Trends\WatchlistShow;
use App\Models\Application;
use App\Models\Trend;
use App\Models\Watchlist;
use Livewire\Livewire;

test('super admin visualiza a lista de watchlists', function () {
    $admin = superAdmin();
    Watchlist::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.trends.watchlists.index'))
        ->assertOk()
        ->assertSeeLivewire(WatchlistIndex::class);
});

test('super admin cria uma watchlist e ela gera trends automaticamente', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    Livewire::actingAs($admin)
        ->test(WatchlistForm::class, ['watchlist' => null])
        ->set('applicationId', $application->id)
        ->set('name', 'Casa')
        ->set('keywords', "cafeteira\nair fryer")
        ->set('hashtags', '#cantinhodocafe')
        ->call('save')
        ->assertRedirect(route('admin.trends.watchlists.index'));

    $watchlist = Watchlist::where('name', 'Casa')->first();
    expect($watchlist)->not->toBeNull();
    expect($watchlist->keywords)->toBe(['cafeteira', 'air fryer']);

    $this->assertDatabaseHas('trends', ['watchlist_id' => $watchlist->id, 'term' => 'cafeteira']);
    $this->assertDatabaseHas('trends', ['watchlist_id' => $watchlist->id, 'term' => 'air fryer']);
    $this->assertDatabaseHas('trends', ['watchlist_id' => $watchlist->id, 'term' => '#cantinhodocafe']);
    $this->assertDatabaseHas('audit_logs', ['action' => 'watchlist.created']);
});

test('editar watchlist removendo um termo desativa a trend correspondente', function () {
    $admin = superAdmin();
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira', 'aspirador'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    Livewire::actingAs($admin)
        ->test(WatchlistForm::class, ['watchlist' => $watchlist])
        ->set('keywords', 'cafeteira')
        ->call('save')
        ->assertRedirect(route('admin.trends.watchlists.index'));

    $this->assertDatabaseHas('trends', ['watchlist_id' => $watchlist->id, 'term' => 'aspirador', 'status' => Trend::STATUS_INACTIVE]);
    $this->assertDatabaseHas('trends', ['watchlist_id' => $watchlist->id, 'term' => 'cafeteira', 'status' => Trend::STATUS_ACTIVE]);
});

test('não é possível excluir uma watchlist com trends vinculadas', function () {
    $admin = superAdmin();
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    Livewire::actingAs($admin)
        ->test(WatchlistIndex::class)
        ->call('delete', $watchlist)
        ->assertHasErrors('watchlist');

    $this->assertDatabaseHas('watchlists', ['id' => $watchlist->id]);
});

test('watchlist show lista as trends e permite coletar agora', function () {
    $admin = superAdmin();
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);
    $trend = Trend::where('watchlist_id', $watchlist->id)->first();

    Livewire::actingAs($admin)
        ->test(WatchlistShow::class, ['watchlist' => $watchlist])
        ->assertSee('cafeteira')
        ->call('collectNow', $trend)
        ->assertHasNoErrors();

    expect($trend->fresh()->last_collected_at)->not->toBeNull();
});

test('watchlist show exibe o trend score já calculado', function () {
    $admin = superAdmin();
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);
    $trend = Trend::where('watchlist_id', $watchlist->id)->first();
    $trend->update(['trend_score' => 87.5]);

    Livewire::actingAs($admin)
        ->test(WatchlistShow::class, ['watchlist' => $watchlist])
        ->assertSee('88');
});

test('administrador só consegue visualizar watchlists, não criar', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.trends.watchlists.index'))
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(WatchlistForm::class, ['watchlist' => null])
        ->assertForbidden();
});
