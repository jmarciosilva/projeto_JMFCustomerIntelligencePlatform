<?php

namespace Tests\Feature\Marketing;

use App\Actions\GenerateProductContentAction;
use App\Domain\Marketing\Contracts\ContentGenerator;
use App\Domain\Marketing\TemplateContentGenerator;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateProductContentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_action_persists_three_content_records(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateProductContentAction(new TemplateContentGenerator);

        $product = ['name' => 'Vaso de Cerâmica', 'category' => 'Artesanato', 'price' => 89.90];

        $records = $action->execute($app->id, 'product', 42, $product);

        $this->assertCount(3, $records);
        $this->assertDatabaseHas('marketing_contents', [
            'application_id' => $app->id,
            'subject_type' => 'product',
            'subject_id' => 42,
            'type' => MarketingContent::TYPE_TITLE,
            'status' => MarketingContent::STATUS_DRAFT,
        ]);
        $this->assertDatabaseHas('marketing_contents', [
            'type' => MarketingContent::TYPE_DESCRIPTION,
        ]);
        $this->assertDatabaseHas('marketing_contents', [
            'type' => MarketingContent::TYPE_SEO_KEYWORDS,
        ]);
    }

    public function test_seo_keywords_stored_in_metadata(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateProductContentAction(new TemplateContentGenerator);

        $product = ['name' => 'Vaso', 'category' => 'Artesanato'];
        $action->execute($app->id, 'product', 1, $product);

        $seoRecord = MarketingContent::where('type', MarketingContent::TYPE_SEO_KEYWORDS)->first();

        $this->assertIsArray($seoRecord->metadata['keywords']);
        $this->assertNotEmpty($seoRecord->metadata['keywords']);
    }

    public function test_records_track_which_generator_was_used(): void
    {
        $app = Application::factory()->create();
        $action = new GenerateProductContentAction(new TemplateContentGenerator);

        $action->execute($app->id, 'product', 1, ['name' => 'Item', 'category' => 'Categoria']);

        $this->assertDatabaseHas('marketing_contents', ['generator' => 'template']);
    }

    public function test_container_resolves_template_driver_by_default(): void
    {
        config(['marketing.driver' => 'template']);

        $generator = app(ContentGenerator::class);

        $this->assertInstanceOf(TemplateContentGenerator::class, $generator);
    }
}
