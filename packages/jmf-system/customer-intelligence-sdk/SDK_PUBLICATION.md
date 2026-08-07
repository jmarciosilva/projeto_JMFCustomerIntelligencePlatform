# SDK Publication Guide

Guia para publicar o JMF Customer Intelligence SDK no Packagist ou como repositório privado.

---

## Opção 1: Publicar no Packagist (Recomendado)

### Passo 1: Preparar o Repositório

```bash
# Verificar composer.json está correto
cat composer.json
# Deve conter:
# - "name": "jmf-system/customer-intelligence-sdk"
# - "version": "1.0.0" (ou superior)
# - "description": descritivo
# - "license": propriedade intelectual ou open source
```

### Passo 2: Criar Repositório Git Público

```bash
# Se já não estiver em um repositório
git init
git add .
git commit -m "feat: SDK v1.0.0 - Initial release"
git tag -a v1.0.0 -m "Version 1.0.0"
git remote add origin https://github.com/jmf-system/customer-intelligence-sdk.git
git push origin main --tags
```

### Passo 3: Registrar no Packagist

1. Acesse [https://packagist.org](https://packagist.org)
2. Faça login com sua conta GitHub
3. Clique em "Submit Package"
4. Cole a URL do repositório: `https://github.com/jmf-system/customer-intelligence-sdk.git`
5. Clique em "Check"
6. Revise e confirme

### Passo 4: Configurar GitHub Webhook (opcional, mas recomendado)

Para sincronizar automaticamente com Packagist:

1. Em [packagist.org/profile](https://packagist.org/profile/)
2. Copie o **API Token**
3. No repositório GitHub → Settings → Webhooks
4. URL: `https://packagist.org/api/github` (POST)
5. Content type: `application/json`
6. Events: Push events
7. Ative

### Passo 5: Testar Instalação

```bash
# Em outra aplicação
composer require jmf-system/customer-intelligence-sdk

# Ou versão específica
composer require jmf-system/customer-intelligence-sdk:^1.0
```

---

## Opção 2: Repositório Privado (Git)

Se não quiser publicar no Packagist ainda:

### Passo 1: Criar Repositório Privado

```bash
# GitHub, GitLab, Bitbucket, etc.
git init --bare customer-intelligence-sdk.git

# Ou clonar de um repositório existente
git clone --bare https://github.com/seu-usuario/customer-intelligence-sdk.git
```

### Passo 2: Configurar Acesso SSH

```bash
# Gerar chave SSH se não tiver
ssh-keygen -t ed25519 -f ~/.ssh/id_ci_sdk

# Adicionar ao seu servidor Git ou GitHub
# Compartilhar a chave com o time
```

### Passo 3: Usar em Outras Aplicações

No `composer.json` da aplicação:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:jmf-system/customer-intelligence-sdk.git",
            "no-api": true
        }
    ],
    "require": {
        "jmf-system/customer-intelligence-sdk": "dev-main"
    }
}
```

```bash
composer install
```

---

## Checklist Pré-Publicação

- [ ] `composer.json` validado: `composer validate`
- [ ] Todos os testes passando: `vendor/bin/pest`
- [ ] Código formatado: `vendor/bin/pint`
- [ ] Análise estática ok: `composer analyse`
- [ ] CHANGELOG.md atualizado com v1.0.0
- [ ] README.md completo e com instruções de instalação
- [ ] PLUGIN_INSTALLATION.md documentado
- [ ] LICENSE.md adicionado
- [ ] .gitignore configurado
- [ ] Git tag criado: `git tag v1.0.0`

---

## Versionamento Semântico

Para versões futuras:

- **Patch** (1.0.1): Bug fixes
- **Minor** (1.1.0): Features novas (backward compatible)
- **Major** (2.0.0): Breaking changes

Exemplo:

```bash
# Para v1.1.0
git tag -a v1.1.0 -m "Add new feature: X"
git push origin v1.1.0

# Packagist atualiza automaticamente (se webhook configurado)
```

---

## Troubleshooting Publicação

**"Package already exists"**
→ Já foi publicado, use outra versão

**"Invalid composer.json"**
→ Execute `composer validate` e corrija erros

**"GitHub token expired"**
→ Regenere em https://packagist.org/profile/

**"Webhook não funciona"**
→ Verifique em GitHub → Settings → Webhooks → Recent Deliveries

---

## Documentos Relacionados

- [README.md](./README.md) — Documentação técnica
- [PLUGIN_INSTALLATION.md](./PLUGIN_INSTALLATION.md) — Guia de instalação
- [CHANGELOG.md](./CHANGELOG.md) — Histórico de versões
