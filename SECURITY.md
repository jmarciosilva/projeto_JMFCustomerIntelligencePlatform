# Segurança e Privacidade — JMF Customer Intelligence

## Princípios

- Privacidade e conformidade com a LGPD desde o início do projeto.
- Nenhuma operação crítica dos sistemas clientes pode depender da disponibilidade desta plataforma.
- Isolamento rigoroso de dados por tenant e por aplicação.

## Autenticação de aplicações (tokens)

- Cada aplicação cliente autentica-se via token próprio (Laravel Sanctum).
- O token completo é exibido **apenas no momento da criação**; a partir daí somente um hash é armazenado.
- Tokens podem ser revogados e rotacionados a qualquer momento pelo administrador do tenant.
- Rate limiting é aplicado por aplicação para conter abuso ou falhas em cascata dos sistemas clientes.
- Validação de origem (quando aplicável) para reduzir uso indevido de tokens vazados.

## Autorização

- Toda operação administrativa é protegida por Policies/Gates.
- Todo acesso a dados é filtrado por `tenant_id` — nenhuma consulta deve retornar dados de outro tenant.
- Ações administrativas sensíveis são registradas em log de auditoria (`audit_logs`).

## Dados de eventos

- `properties` e `context` têm tamanho máximo validado na ingestão (Fase 04), prevenindo payloads abusivos.
- É proibido armazenar senhas, dados de cartão de crédito, documentos ou qualquer conteúdo sensível dentro de eventos.
- Prevenção de duplicidade obrigatória via `event_id` + aplicação (idempotência).
- Proteção contra mass assignment em todos os Models (uso de `$fillable`/`$guarded` explícitos).

## Privacidade e LGPD

- Consentimento do titular é registrado (`contact_consents`), antes de qualquer processamento que exija base legal específica.
- Contatos podem ser anonimizados ou excluídos mediante solicitação, preservando a integridade agregada de métricas quando possível.
- Política de retenção de dados será definida e documentada antes da Fase 11 (Produção).
- Dados pessoais não são compartilhados entre tenants nem entre aplicações sem base legal e finalidade explícitas.

## Boas práticas de desenvolvimento

- Nunca commitar credenciais, `.env` ou segredos no repositório.
- Utilizar variáveis de ambiente para todas as credenciais e chaves.
- Validar e sanitizar toda entrada externa via Form Requests.
- Utilizar HTTPS em produção (fora do escopo do ambiente local de desenvolvimento).
- Dependências mantidas atualizadas; avaliar avisos de segurança do Composer/NPM regularmente.

## Reportando problemas de segurança

Por se tratar de um projeto em desenvolvimento privado, problemas de segurança devem ser reportados diretamente à equipe responsável pelo projeto, sem divulgação pública.
