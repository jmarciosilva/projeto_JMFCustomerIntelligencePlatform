<?php

namespace App\Domain\Marketing;

use App\Domain\Marketing\Contracts\ContentGenerator;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicContentGenerator implements ContentGenerator
{
    public function __construct(
        private string $apiKey,
        private string $model,
        private string $baseUrl,
        private int $maxTokens,
    ) {}

    public function generateTitle(array $product): string
    {
        return $this->complete(
            "Crie um título curto e persuasivo (máx. 70 caracteres) para um anúncio de marketplace do produto \"{$product['name']}\" ".
            "da categoria \"{$product['category']}\". Responda apenas com o título, sem aspas ou explicações."
        );
    }

    public function generateDescription(array $product): string
    {
        $context = $this->productContext($product);

        return $this->complete(
            "Escreva uma descrição de produto persuasiva (3-5 frases) para um marketplace de pequenos empreendedores. {$context} ".
            'Responda apenas com a descrição, sem títulos ou explicações.'
        );
    }

    public function generateSeoKeywords(array $product): array
    {
        $response = $this->complete(
            "Liste 10 palavras-chave de SEO relevantes para o produto \"{$product['name']}\" da categoria \"{$product['category']}\". ".
            'Responda apenas com as palavras separadas por vírgula, sem numeração ou explicações.'
        );

        return array_values(array_filter(array_map('trim', explode(',', $response))));
    }

    public function generateSocialText(array $product, string $platform): string
    {
        $context = $this->productContext($product);

        return $this->complete(
            "Escreva um texto promocional curto para {$platform} sobre o produto \"{$product['name']}\". {$context} ".
            'Responda apenas com o texto do post, sem explicações.'
        );
    }

    public function generateHashtags(array $product): array
    {
        $response = $this->complete(
            "Liste 5 hashtags relevantes (com #) para divulgar o produto \"{$product['name']}\" da categoria \"{$product['category']}\" ".
            'em uma feira/marketplace de pequenos empreendedores. Responda apenas com as hashtags separadas por espaço, sem explicações.'
        );

        return array_values(array_filter(preg_split('/\s+/', trim($response)) ?: []));
    }

    public function generateEmailCampaign(array $product): array
    {
        $context = $this->productContext($product);

        $response = $this->complete(
            "Escreva uma campanha de e-mail marketing curta para o produto \"{$product['name']}\". {$context} ".
            "Responda no formato exato:\nASSUNTO: <linha de assunto>\nCORPO: <corpo do e-mail>"
        );

        if (preg_match('/ASSUNTO:\s*(.*?)\s*CORPO:\s*(.*)/s', $response, $matches)) {
            return ['subject' => trim($matches[1]), 'body' => trim($matches[2])];
        }

        return ['subject' => $product['name'], 'body' => $response];
    }

    private function productContext(array $product): string
    {
        $price = isset($product['price']) ? " Preço: R$ {$product['price']}." : '';
        $description = isset($product['description']) ? " Detalhes: {$product['description']}." : '';

        return "Categoria: {$product['category']}.{$price}{$description}";
    }

    private function complete(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Anthropic API key não configurada. Defina ANTHROPIC_API_KEY no .env para usar este driver.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($this->baseUrl, [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $response->throw();

        return trim($response->json('content.0.text', ''));
    }
}
