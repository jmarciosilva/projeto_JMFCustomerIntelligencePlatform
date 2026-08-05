<?php

namespace JmfSystem\CustomerIntelligence\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use JmfSystem\CustomerIntelligence\Support\VisitorContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve (ou cria) o visitor_id e o session_id da requisição atual via
 * cookies e os disponibiliza em VisitorContext para o Client. O cookie de
 * sessão é sempre renovado (TTL rolante) — se o navegador não enviar mais
 * requisições dentro da janela configurada, ele expira e uma nova sessão
 * é iniciada automaticamente, mantendo o mesmo visitor_id.
 */
class ResolveVisitorAndSession
{
    public function __construct(private readonly VisitorContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $visitorCookie = config('customer-intelligence.visitor_cookie');
        $sessionCookie = config('customer-intelligence.session_cookie');

        $visitorId = $request->cookie($visitorCookie['name']) ?: (string) Str::ulid();
        $sessionId = $request->cookie($sessionCookie['name']) ?: (string) Str::ulid();

        $this->context->setVisitorId($visitorId);
        $this->context->setSessionId($sessionId);

        Cookie::queue($visitorCookie['name'], $visitorId, $visitorCookie['minutes']);
        Cookie::queue($sessionCookie['name'], $sessionId, $sessionCookie['minutes']);

        return $next($request);
    }
}
