<?php

namespace App\Domain\Analytics;

/**
 * Templates de exemplo derivados de EVENT_CATALOG.md. Cobrem apenas as etapas
 * com um `event_name` claro no catálogo — etapas ambíguas (ex.: "Oportunidade
 * profissional", "Cliente recorrente") ficam de fora do MVP da Fase 06.
 */
class FunnelTemplates
{
    /**
     * @return array<string, array{label: string, steps: list<string>}>
     */
    public static function all(): array
    {
        return [
            'site-pessoal' => [
                'label' => 'Site pessoal',
                'steps' => [
                    'session.started',
                    'article.viewed',
                    'article.completed',
                    'project.viewed',
                    'contact.form_started',
                    'contact.form_submitted',
                ],
            ],
            'clube-do-salao' => [
                'label' => 'Clube do Salão',
                'steps' => [
                    'session.started',
                    'service.viewed',
                    'professional.selected',
                    'appointment.started',
                    'appointment.created',
                    'appointment.completed',
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, steps: list<string>}|null
     */
    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
