<?php

namespace Tests\Feature\Marketing;

use App\Actions\GenerateEmailCampaignAction;
use App\Domain\Marketing\TemplateContentGenerator;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateEmailCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_and_persists_email_campaign(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateEmailCampaignAction(new TemplateContentGenerator);

        $record = $action->execute($app->id, 'product', 1, ['name' => 'Vaso', 'category' => 'Artesanato']);

        $this->assertEquals(MarketingContent::TYPE_EMAIL_CAMPAIGN, $record->type);
        $this->assertNotEmpty($record->content);
        $this->assertArrayHasKey('subject', $record->metadata);
    }
}
