<?php

namespace App\Domain\Marketing;

use App\Domain\Marketing\Contracts\ContentGenerator;

class TemplateContentGenerator implements ContentGenerator
{
    public function generateTitle(array $product): string
    {
        return "{$product['name']} — {$product['category']} de Qualidade | Feira Esquerda Livre";
    }

    public function generateDescription(array $product): string
    {
        $priceText = isset($product['price'])
            ? ' Por apenas R$ '.number_format((float) $product['price'], 2, ',', '.').'.'
            : '';

        $extra = trim($product['description'] ?? '');
        $extraText = $extra !== '' ? " {$extra}" : '';

        return "Conheça {$product['name']}, um produto exclusivo da categoria {$product['category']}.".
            $priceText.$extraText.
            ' Compre agora e apoie o comércio local e sustentável.';
    }

    public function generateSeoKeywords(array $product): array
    {
        $nameTokens = $this->tokenize($product['name']);
        $categoryTokens = $this->tokenize($product['category']);

        $keywords = array_values(array_unique(array_merge(
            $nameTokens,
            $categoryTokens,
            [
                strtolower($product['category']),
                'comprar '.strtolower($product['category']),
                'feira esquerda livre',
            ]
        )));

        return array_slice($keywords, 0, 10);
    }

    public function generateSocialText(array $product, string $platform): string
    {
        $hashtags = implode(' ', $this->generateHashtags($product));

        return match ($platform) {
            'instagram' => "✨ {$product['name']} chegou! Confira essa novidade na categoria {$product['category']}. {$hashtags}",
            'facebook' => "Você já viu {$product['name']}? É a novidade em {$product['category']} que está fazendo sucesso na Feira Esquerda Livre. Vem conferir!",
            'whatsapp' => "Oi! Dá uma olhada em {$product['name']} 👀 — {$product['category']} direto da Feira Esquerda Livre.",
            default => "{$product['name']} — {$product['category']}",
        };
    }

    public function generateHashtags(array $product): array
    {
        $categoryTag = '#'.str_replace(' ', '', ucwords(strtolower($product['category'])));

        return array_values(array_unique([
            $categoryTag,
            '#feiraesquerdalivre',
            '#comerciolocal',
            '#artesanato',
            '#compredoartesanato',
        ]));
    }

    public function generateEmailCampaign(array $product): array
    {
        $subject = "Novidade: {$product['name']} já está disponível!";

        $priceText = isset($product['price'])
            ? ' por apenas R$ '.number_format((float) $product['price'], 2, ',', '.')
            : '';

        $body = "Olá!\n\n".
            "Temos uma novidade especial para você: {$product['name']}, disponível agora na categoria {$product['category']}{$priceText}.\n\n".
            "Não perca a chance de conferir esse produto exclusivo.\n\n".
            'Um abraço, Feira Esquerda Livre.';

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $words = preg_split('/\s+/', strtolower(trim($text))) ?: [];

        return array_values(array_filter($words, fn ($word) => mb_strlen($word) >= 3));
    }
}
