<?php

use App\Domain\Affiliate\CommercialIntentClassifier;
use App\Models\ProductOpportunity;

describe('CommercialIntentClassifier', function () {
    beforeEach(function () {
        $this->classifier = app(CommercialIntentClassifier::class);
    });

    it('classifica alta intenção de compra', function () {
        $intent = $this->classifier->classify('melhor smartphone preço');
        expect($intent)->toBe(ProductOpportunity::INTENT_HIGH);

        $intent = $this->classifier->classify('onde comprar iPhone');
        expect($intent)->toBe(ProductOpportunity::INTENT_HIGH);

        $intent = $this->classifier->classify('quanto custa notebook');
        expect($intent)->toBe(ProductOpportunity::INTENT_HIGH);
    });

    it('classifica média intenção de compra', function () {
        $intent = $this->classifier->classify('guia de fones');
        expect($intent)->toBe(ProductOpportunity::INTENT_MEDIUM);

        $intent = $this->classifier->classify('top 10 smartphones');
        expect($intent)->toBe(ProductOpportunity::INTENT_MEDIUM);

        $intent = $this->classifier->classify('como escolher TV');
        expect($intent)->toBe(ProductOpportunity::INTENT_MEDIUM);
    });

    it('classifica baixa intenção de compra', function () {
        $intent = $this->classifier->classify('o que é smartphone');
        expect($intent)->toBe(ProductOpportunity::INTENT_LOW);

        $intent = $this->classifier->classify('história dos computadores');
        expect($intent)->toBe(ProductOpportunity::INTENT_LOW);

        $intent = $this->classifier->classify('como funciona a internet');
        expect($intent)->toBe(ProductOpportunity::INTENT_LOW);
    });

    it('retorna null para termos sem intenção clara', function () {
        $intent = $this->classifier->classify('xyz123abc');
        expect($intent)->toBeNull();

        $intent = $this->classifier->classify('asdfghjkl');
        expect($intent)->toBeNull();
    });

    it('case-insensitive matching', function () {
        $intent1 = $this->classifier->classify('COMPRAR SMARTPHONE');
        $intent2 = $this->classifier->classify('comprar smartphone');
        $intent3 = $this->classifier->classify('Comprar Smartphone');

        expect($intent1)->toBe($intent2);
        expect($intent2)->toBe($intent3);
        expect($intent1)->toBe(ProductOpportunity::INTENT_HIGH);
    });

    it('scoreByIntent retorna valores corretos', function () {
        expect($this->classifier->scoreByIntent(ProductOpportunity::INTENT_HIGH))->toBe(100.0);
        expect($this->classifier->scoreByIntent(ProductOpportunity::INTENT_MEDIUM))->toBe(50.0);
        expect($this->classifier->scoreByIntent(ProductOpportunity::INTENT_LOW))->toBe(25.0);
        expect($this->classifier->scoreByIntent(null))->toBe(0.0);
    });

    it('detecta palavras-chave mesmo em contexto maior', function () {
        $intent = $this->classifier->classify('preciso encontrar o melhor preço de um notebook novo');
        expect($intent)->toBe(ProductOpportunity::INTENT_HIGH);

        $intent = $this->classifier->classify('estou procurando um guia de como escolher uma câmera digital');
        expect($intent)->toBe(ProductOpportunity::INTENT_MEDIUM);
    });
});
