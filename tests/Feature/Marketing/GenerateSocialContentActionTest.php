<?php

namespace Tests\Feature\Marketing;

use App\Actions\GenerateSocialContentAction;
use App\Domain\Marketing\TemplateContentGenerator;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateSocialContentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_content_for_three_platforms(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateSocialContentAction(new TemplateContentGenerator);

        $records = $action->execute($app->id, 'product', 1, ['name' => 'Vaso', 'category' => 'Artesanato']);

        $this->assertCount(3, $records);
        $this->assertDatabaseHas('marketing_contents', ['type' => MarketingContent::TYPE_SOCIAL_INSTAGRAM]);
        $this->assertDatabaseHas('marketing_contents', ['type' => MarketingContent::TYPE_SOCIAL_FACEBOOK]);
        $this->assertDatabaseHas('marketing_contents', ['type' => MarketingContent::TYPE_SOCIAL_WHATSAPP]);
    }

    public function test_hashtags_stored_in_metadata(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateSocialContentAction(new TemplateContentGenerator);

        $action->execute($app->id, 'product', 1, ['name' => 'Vaso', 'category' => 'Artesanato']);

        $record = MarketingContent::where('type', MarketingContent::TYPE_SOCIAL_INSTAGRAM)->first();

        $this->assertIsArray($record->metadata['hashtags']);
        $this->assertNotEmpty($record->metadata['hashtags']);
    }
}
