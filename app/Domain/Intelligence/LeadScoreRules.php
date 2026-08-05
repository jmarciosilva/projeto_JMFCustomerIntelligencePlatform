<?php

namespace App\Domain\Intelligence;

/**
 * Pontuação por event_name usada por ComputeLeadScoresAction. Eventos fora
 * deste mapa não pontuam (0) — só o que está explicitamente listado conta,
 * evitando ruído de eventos futuros ainda não avaliados. Cobre os catálogos
 * já documentados em EVENT_CATALOG.md (Site pessoal e Clube do Salão).
 * Ajustável livremente, sem necessidade de migração.
 */
class LeadScoreRules
{
    /**
     * @return array<string, int>
     */
    public static function points(): array
    {
        return [
            // Site pessoal
            'page.viewed' => 1,
            'session.started' => 1,
            'article.viewed' => 2,
            'article.completed' => 4,
            'article.shared' => 3,
            'article.cta_clicked' => 5,
            'category.viewed' => 1,
            'project.viewed' => 2,
            'project.repository_clicked' => 3,
            'project.demo_clicked' => 3,
            'contact.form_started' => 5,
            'contact.form_submitted' => 20,
            'whatsapp.clicked' => 8,
            'email.clicked' => 6,
            'linkedin.clicked' => 4,
            'resume.downloaded' => 10,
            'newsletter.subscribed' => 8,

            // Clube do Salão
            'service.viewed' => 2,
            'service.favorited' => 4,
            'professional.viewed' => 2,
            'professional.selected' => 4,
            'appointment.started' => 6,
            'appointment.created' => 15,
            'appointment.confirmed' => 5,
            'appointment.rescheduled' => 2,
            'appointment.completed' => 25,
            'payment.completed' => 25,
            'subscription.created' => 20,
            'loyalty.reward_earned' => 5,
            'review.created' => 6,
            'coupon.used' => 5,
        ];
    }

    public static function pointsFor(string $eventName): int
    {
        return self::points()[$eventName] ?? 0;
    }
}
