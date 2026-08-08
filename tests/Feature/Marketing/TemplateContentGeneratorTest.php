<?php

namespace Tests\Feature\Marketing;

use App\Domain\Marketing\TemplateContentGenerator;
use Tests\TestCase;

class TemplateContentGeneratorTest extends TestCase
{
    private TemplateContentGenerator $generator;

    private array $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new TemplateContentGenerator;
        $this->product = [
            'name' => 'Vaso de Cerâmica Artesanal',
            'category' => 'Artesanato',
            'price' => 89.90,
            'description' => 'Feito à mão com argila local.',
        ];
    }

    public function test_generates_title_containing_product_name(): void
    {
        $title = $this->generator->generateTitle($this->product);

        $this->assertStringContainsString('Vaso de Cerâmica Artesanal', $title);
    }

    public function test_generates_description_containing_price(): void
    {
        $description = $this->generator->generateDescription($this->product);

        $this->assertStringContainsString('89,90', $description);
        $this->assertStringContainsString('Artesanato', $description);
    }

    public function test_generates_description_without_price_when_missing(): void
    {
        $product = ['name' => 'Item', 'category' => 'Categoria'];

        $description = $this->generator->generateDescription($product);

        $this->assertStringNotContainsString('R$', $description);
    }

    public function test_generates_seo_keywords_limited_to_ten(): void
    {
        $keywords = $this->generator->generateSeoKeywords($this->product);

        $this->assertLessThanOrEqual(10, count($keywords));
        $this->assertContains('artesanato', $keywords);
    }

    public function test_generates_social_text_per_platform(): void
    {
        $instagram = $this->generator->generateSocialText($this->product, 'instagram');
        $facebook = $this->generator->generateSocialText($this->product, 'facebook');
        $whatsapp = $this->generator->generateSocialText($this->product, 'whatsapp');

        $this->assertStringContainsString('Vaso de Cerâmica Artesanal', $instagram);
        $this->assertStringContainsString('Vaso de Cerâmica Artesanal', $facebook);
        $this->assertStringContainsString('Vaso de Cerâmica Artesanal', $whatsapp);
        $this->assertNotEquals($instagram, $facebook);
    }

    public function test_generates_hashtags_including_category(): void
    {
        $hashtags = $this->generator->generateHashtags($this->product);

        $this->assertContains('#Artesanato', $hashtags);
        $this->assertContains('#feiraesquerdalivre', $hashtags);

        foreach ($hashtags as $hashtag) {
            $this->assertStringStartsWith('#', $hashtag);
            $this->assertStringNotContainsString(' ', $hashtag);
        }
    }

    public function test_generates_email_campaign_with_subject_and_body(): void
    {
        $campaign = $this->generator->generateEmailCampaign($this->product);

        $this->assertArrayHasKey('subject', $campaign);
        $this->assertArrayHasKey('body', $campaign);
        $this->assertStringContainsString('Vaso de Cerâmica Artesanal', $campaign['subject']);
        $this->assertStringContainsString('89,90', $campaign['body']);
    }
}
