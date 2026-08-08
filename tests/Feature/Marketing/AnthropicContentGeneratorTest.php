<?php

namespace Tests\Feature\Marketing;

use App\Domain\Marketing\AnthropicContentGenerator;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AnthropicContentGeneratorTest extends TestCase
{
    private array $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = ['name' => 'Vaso de Cerâmica', 'category' => 'Artesanato', 'price' => 89.90];
    }

    private function fakeResponse(string $text): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => $text]],
            ], 200),
        ]);
    }

    public function test_generate_title_sends_request_and_parses_response(): void
    {
        $this->fakeResponse('Vaso Artesanal Exclusivo');

        $generator = new AnthropicContentGenerator('fake-key', 'claude-3-5-haiku-20241022', 'https://api.anthropic.com/v1/messages', 1024);

        $title = $generator->generateTitle($this->product);

        $this->assertEquals('Vaso Artesanal Exclusivo', $title);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'fake-key')
                && $request['model'] === 'claude-3-5-haiku-20241022';
        });
    }

    public function test_generate_seo_keywords_splits_comma_separated_response(): void
    {
        $this->fakeResponse('cerâmica, vaso, artesanato, decoração, casa');

        $generator = new AnthropicContentGenerator('fake-key', 'claude-3-5-haiku-20241022', 'https://api.anthropic.com/v1/messages', 1024);

        $keywords = $generator->generateSeoKeywords($this->product);

        $this->assertCount(5, $keywords);
        $this->assertContains('cerâmica', $keywords);
    }

    public function test_generate_hashtags_splits_whitespace_separated_response(): void
    {
        $this->fakeResponse('#ceramica #vaso #artesanato');

        $generator = new AnthropicContentGenerator('fake-key', 'claude-3-5-haiku-20241022', 'https://api.anthropic.com/v1/messages', 1024);

        $hashtags = $generator->generateHashtags($this->product);

        $this->assertCount(3, $hashtags);
    }

    public function test_generate_email_campaign_parses_subject_and_body(): void
    {
        $this->fakeResponse("ASSUNTO: Novo vaso disponível\nCORPO: Confira nosso novo vaso artesanal.");

        $generator = new AnthropicContentGenerator('fake-key', 'claude-3-5-haiku-20241022', 'https://api.anthropic.com/v1/messages', 1024);

        $campaign = $generator->generateEmailCampaign($this->product);

        $this->assertEquals('Novo vaso disponível', $campaign['subject']);
        $this->assertEquals('Confira nosso novo vaso artesanal.', $campaign['body']);
    }

    public function test_throws_exception_when_api_key_missing(): void
    {
        $generator = new AnthropicContentGenerator('', 'claude-3-5-haiku-20241022', 'https://api.anthropic.com/v1/messages', 1024);

        $this->expectException(RuntimeException::class);

        $generator->generateTitle($this->product);
    }
}
