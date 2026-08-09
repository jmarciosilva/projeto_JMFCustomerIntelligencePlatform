<?php

namespace App\Domain\Trends\Contracts;

use App\Domain\Trends\Exceptions\ProviderNotConfiguredException;
use App\Models\Trend;

interface TrendProviderInterface
{
    /**
     * Identificador único do provider (ex.: "manual", "internal_behavior", "instagram").
     */
    public function key(): string;

    /**
     * Indica se o provider está pronto para coletar sinais automaticamente
     * (ex.: credenciais/API oficial disponível). Providers sem integração
     * oficial disponível devem retornar false — o job de coleta os ignora
     * silenciosamente, sem tentar chamar collect().
     */
    public function isConfigured(): bool;

    /**
     * Coleta sinais de tendência para o termo/hashtag monitorado.
     *
     * Retorna `null` quando não há dado novo a registrar (ex.: provider
     * manual, sem coleta automática). Quando retorna um array, um novo
     * `TrendSnapshot` é criado com esses valores.
     *
     * @return array{mentions?: int|null, engagement?: int|null, velocity?: float|null, score?: float|null, metadata?: array<string, mixed>|null}|null
     *
     * @throws ProviderNotConfiguredException quando o provider não tem integração oficial disponível.
     */
    public function collect(Trend $trend): ?array;
}
