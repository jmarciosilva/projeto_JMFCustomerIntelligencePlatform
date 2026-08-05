<?php

namespace JmfSystem\CustomerIntelligence\Support;

/**
 * Guarda o visitor_id/session_id resolvidos para a requisição atual.
 * Registrado como singleton no container — preenchido pelo middleware
 * ResolveVisitorAndSession e consultado pelo Client.
 */
class VisitorContext
{
    private ?string $visitorId = null;

    private ?string $sessionId = null;

    public function setVisitorId(string $visitorId): void
    {
        $this->visitorId = $visitorId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function visitorId(): ?string
    {
        return $this->visitorId;
    }

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }
}
