# Contribuição — JMF Customer Intelligence

## Antes de começar

Leia obrigatoriamente, nesta ordem:

1. `README.md`
2. `ROADMAP.md`
3. `ARCHITECTURE.md`
4. `EVENT_CATALOG.md`
5. `SECURITY.md`

Nenhuma funcionalidade fora da fase atual do `ROADMAP.md` deve ser implementada sem aprovação explícita.

## Fluxo de trabalho

- Trabalhar por fases curtas e verificáveis, conforme o `ROADMAP.md`.
- Cada fase deve ser concluída com testes automatizados e documentação atualizada antes de avançar.
- Commits pequenos e descritivos.
- Nunca duplicar código entre módulos ou projetos; reutilizar através de Actions/Services/pacotes.

## Regras gerais

Nunca:

- colocar regra de negócio em Controllers;
- acessar Models diretamente em Views;
- duplicar código;
- criar funcionalidades fora do Roadmap.

Sempre:

- utilizar Actions para casos de uso;
- utilizar Form Requests para validação;
- utilizar Policies para autorização;
- criar testes (Feature e Unit, usando Pest);
- documentar mudanças relevantes.

## Antes de abrir um Pull Request

Executar e garantir sucesso em todos os comandos:

```bash
vendor/bin/pint
composer analyse
composer refactor
php artisan test
npm run build
```

Nenhum Pull Request deve ser aprovado sem todos os testes passando.

## Padrões de código

- PSR-12, SOLID, Clean Code.
- Laravel Best Practices.
- Domain Driven Design apenas quando agregar valor real.
- Controllers pequenos e sem lógica de negócio.
- Nomes de classes, métodos e variáveis descritivos, em inglês, seguindo as convenções já usadas no projeto.

## Estrutura de commits sugerida

```text
tipo(escopo): descrição curta no imperativo

Exemplo:
feat(events): adiciona endpoint de ingestão de eventos
fix(auth): corrige validação de token expirado
docs(readme): atualiza instruções de instalação
```
