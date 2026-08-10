<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateClick;
use App\Models\AffiliateLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkRedirectController extends Controller
{
    /**
     * Redireciona para o link de afiliado e registra o clique
     * GET /go/{slug}
     */
    public function redirect(Request $request, string $slug)
    {
        $link = AffiliateLink::where('slug', $slug)->firstOrFail();

        // Verificar se link está ativo
        if (!$link->isActive()) {
            return response()->json(['error' => 'Link inactive'], 404);
        }

        // Registrar o clique
        $this->recordClick($link, $request);

        // Incrementar contador de cliques
        $link->increment('clicks');

        // Construir URL de redirecionamento com UTMs
        $redirectUrl = $this->buildRedirectUrl($link, $request);

        // Redirecionar 302 (temporário)
        return redirect()->temporary($redirectUrl);
    }

    /**
     * Registra um clique no banco de dados
     */
    private function recordClick(AffiliateLink $link, Request $request): void
    {
        $ipHash = null;
        if ($ip = $request->ip()) {
            $ipHash = hash('sha256', $ip);
        }

        AffiliateClick::create([
            'affiliate_link_id' => $link->id,
            'visitor_id' => $this->getVisitorId($request),
            'source' => $request->query('utm_source'),
            'medium' => $request->query('utm_medium'),
            'referer' => $request->header('referer'),
            'user_agent' => $request->header('user-agent'),
            'ip_hash' => $ipHash,
            'clicked_at' => now(),
        ]);
    }

    /**
     * Obtém ou gera um visitor ID anônimo (usando cookie)
     */
    private function getVisitorId(Request $request): string
    {
        $visitorId = $request->cookie('jmf_visitor_id');

        if (!$visitorId) {
            $visitorId = (string) Str::uuid();
        }

        return $visitorId;
    }

    /**
     * Constrói a URL de redirecionamento, preservando UTMs fornecidos
     */
    private function buildRedirectUrl(AffiliateLink $link, Request $request): string
    {
        $url = $link->affiliate_url;

        // Coletar todos os parâmetros existentes
        $params = $request->query();

        // Se o link tem UTMs configurados, usá-los como defaults (mas query string sobrescreve)
        if ($link->utm_source && !isset($params['utm_source'])) {
            $params['utm_source'] = $link->utm_source;
        }
        if ($link->utm_medium && !isset($params['utm_medium'])) {
            $params['utm_medium'] = $link->utm_medium;
        }
        if ($link->utm_campaign && !isset($params['utm_campaign'])) {
            $params['utm_campaign'] = $link->utm_campaign;
        }

        if (empty($params)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($params);
    }
}
