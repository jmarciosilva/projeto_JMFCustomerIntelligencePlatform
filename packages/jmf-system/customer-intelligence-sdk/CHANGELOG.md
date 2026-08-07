# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [1.0.0] - 2026-08-07

### Adicionado

- **SDK Core**: Cliente completo para Customer Intelligence (track, identify, conversion)
- **Configuração**: Totalmente via `.env` com validação de boot
- **Validação de Payload**: Detecção de dados sensíveis, tamanho máximo, campos obrigatórios
- **Retry Inteligente**: Exponential backoff [5s, 30s, 120s] para erros transitórios
- **Logging Estruturado**: Trace IDs (ULID) para rastreamento ponta-a-ponta
- **Health Check**: `Client::healthCheck()` para validar conexão com a API
- **Middleware**: Gerenciamento automático de cookies (visitor_id, session_id)
- **Queue Support**: Integração com filas do Laravel (sync, database, redis, etc)
- **Testes**: 45+ testes com 100% coverage das funcionalidades

### Incluso

- Laravel 11+ e 12 suportados
- PHP 8.2+
- Documentação completa (README, .env.example)
- PSR-12 compliant
- PHPStan level 8

### Notas

Primeira release estável do SDK. Pronto para uso em produção.

---

**Formato inspirado em [Keep a Changelog](https://keepachangelog.com)**
