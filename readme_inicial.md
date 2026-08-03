# JMF Customer Intelligence Platform

<p align="center">

# 🧠 JMF Customer Intelligence Platform

### Customer Data Platform • CRM • Analytics • Marketing Automation • Recommendation Engine

**Uma plataforma central para coletar eventos, compreender o comportamento dos usuários e transformar dados em inteligência de negócio.**

---

**Status:** 🚧 Em Desenvolvimento (MVP)

</p>

---

# Sumário

- Visão Geral
- Objetivos
- Problema
- Solução
- Casos de Uso
- Arquitetura
- Filosofia do Projeto
- Público-alvo
- Stack Tecnológica
- Estrutura do Projeto
- Módulos
- Roadmap
- Padrões Arquiteturais
- Regras Gerais
- Instalação
- Ambiente Local
- Qualidade
- Documentação
- Contribuição
- Licença

---

# Visão Geral

A **JMF Customer Intelligence Platform** é uma plataforma de inteligência de clientes desenvolvida para centralizar informações comportamentais provenientes de diferentes sistemas.

O projeto não é apenas um CRM.

Também não é apenas uma ferramenta de Analytics.

Seu objetivo é criar uma visão única do comportamento dos usuários para permitir:

- CRM
- Customer Data Platform (CDP)
- Analytics
- Marketing Automation
- Segmentação
- Recomendação
- Inteligência Artificial

A plataforma recebe eventos de diferentes aplicações e transforma essas informações em conhecimento útil para empresas.

---

# Objetivos

O objetivo principal do projeto é permitir que qualquer sistema desenvolvido pela JMF System envie eventos para uma plataforma central.

Esses eventos serão utilizados para:

- compreender o comportamento dos usuários;
- identificar interesses;
- construir jornadas;
- calcular afinidade;
- gerar recomendações;
- automatizar campanhas;
- aumentar conversões;
- melhorar retenção.

---

# Problema

Hoje cada sistema possui seu próprio CRM.

Cada sistema possui seu próprio Analytics.

Cada sistema possui seu próprio histórico.

Isso gera:

- duplicação de código;
- manutenção difícil;
- visão fragmentada do cliente;
- dificuldade para reutilização.

---

# Solução

A solução proposta é criar uma plataforma central.

Cada sistema enviará eventos através de um SDK Laravel ou API HTTP.

```
Aplicações

↓

SDK Laravel

↓

API Central

↓

Customer Intelligence Platform

↓

CRM
Analytics
Campanhas
Recomendações
```

A lógica de negócio continua em cada sistema.

A inteligência permanece centralizada.

---

# Casos de Uso

Inicialmente a plataforma será utilizada pelos seguintes sistemas.

## Site Pessoal

- Blog
- Portfólio
- Landing Pages
- Newsletter

---

## Clube do Salão

- Agenda
- Clientes
- Serviços
- Fidelidade
- Marketing

---

## Meu Canto Ideal

- Catálogo
- Blog
- Coleções
- Links afiliados

---

## Feira Esquerda Livre

- Marketplace
- Produtos
- Eventos
- Expositores

---

## Projetos futuros

A arquitetura foi projetada para ser reutilizada em qualquer sistema Laravel.

---

# Filosofia do Projeto

Este projeto segue alguns princípios fundamentais.

## Simplicidade

A solução mais simples sempre será preferida.

---

## Evolução incremental

Nada será desenvolvido antes da fase correspondente.

O projeto evoluirá através de MVPs.

---

## Reutilização

O código deve ser reutilizado entre aplicações.

Nunca copiar módulos inteiros para outro projeto.

---

## Eventos primeiro

Toda inteligência nasce a partir de eventos.

O sistema é orientado a eventos.

---

## Testabilidade

Toda funcionalidade importante deverá possuir testes.

---

## Baixo acoplamento

Controllers pequenos.

Actions.

Services.

Jobs.

Events.

DTOs.

Policies.

---

# Público-alvo

Inicialmente a plataforma será utilizada internamente.

Posteriormente poderá atender:

- salões
- clínicas
- marketplaces
- e-commerces
- escritórios
- pequenas empresas
- produtores de conteúdo

---

# Arquitetura

```
Aplicação

↓

Controller

↓

Action

↓

Service

↓

Event

↓

Job

↓

Database
```

Toda regra de negócio deve permanecer nas Actions e Services.

Controllers apenas recebem requisições.

---

# Stack Tecnológica

## Backend

- Laravel 12

- PHP 8.3+

- MySQL 8

---

## Frontend

- Blade

- Livewire 3

- Alpine.js

- Tailwind CSS 4

---

## Build

- Node.js 22+

- Vite

---

## Qualidade

- Laravel Pint

- PHPStan

- Rector

- PHPUnit

---

## Ambiente Local

- Laragon

- Apache

- MySQL

---

# Infraestrutura MVP

Durante o MVP a plataforma será compatível com hospedagem compartilhada.

Não utilizar inicialmente:

- Redis

- Horizon

- Supervisor

- WebSockets

A infraestrutura inicial utilizará:

- Database Queue

- Database Cache

- Cron

A migração para Redis será realizada somente quando necessária.

---

# Estrutura do Projeto

```
app/

Application/

Domain/

Infrastructure/

Http/

Models/

Policies/

Jobs/

Events/

Services/

Actions/

resources/

routes/

tests/

docs/
```

---

# Módulos

## Core

- Tenants

- Applications

- Usuários

- Perfis

- Auditoria

---

## Customer Data Platform

- Visitantes

- Sessões

- Eventos

- Contatos

---

## CRM

- Timeline

- Tags

- Segmentos

- Lead Score

---

## Analytics

- KPIs

- Funis

- Conversões

- UTMs

- Dashboards

---

## Campanhas

- Newsletter

- Públicos

- Métricas

---

## Recomendações

- Popularidade

- Afinidade

- Produtos relacionados

- IA

---

# Roadmap

O desenvolvimento seguirá estritamente o documento:

```
ROADMAP.md
```

Cada fase somente será iniciada após a conclusão da anterior.

---

# Padrões Arquiteturais

O projeto seguirá:

- SOLID

- Clean Code

- PSR-12

- Laravel Best Practices

- Domain Driven Design (quando agregar valor)

---

# Regras Gerais

Nunca:

- colocar regra de negócio em Controllers;

- acessar Models diretamente em Views;

- duplicar código;

- criar funcionalidades fora do Roadmap.

Sempre:

- utilizar Actions;

- utilizar Form Requests;

- utilizar Policies;

- criar testes;

- documentar mudanças.

---

# Ambiente Local

Configuração atual do projeto.

```
PHP 8.3.30

Node.js 22.12.0

Laragon

Apache

MySQL

Porta HTTP: 80

Porta MySQL: 3306
```

---

# Instalação

```
composer install

npm install

php artisan key:generate

php artisan migrate

npm run build
```

Para desenvolvimento:

```
npm run dev

php artisan serve
```

Ou utilizando o Virtual Host configurado no Laragon.

---

# Qualidade

Executar sempre:

```
vendor/bin/pint

composer analyse

composer refactor

php artisan test
```

Nenhum Pull Request deverá ser aprovado sem todos os testes passando.

---

# Documentação

Toda documentação do projeto encontra-se na pasta:

```
docs/
```

Documentos principais:

- ROADMAP.md

- ARCHITECTURE.md

- DOMAIN.md

- EVENTS.md

- DEVELOPMENT_RULES.md

---

# Inteligência Artificial

Este projeto foi planejado para desenvolvimento assistido por Inteligência Artificial.

Antes de qualquer implementação a IA deverá obrigatoriamente ler:

- README.md

- ROADMAP.md

- docs/ARCHITECTURE.md

- docs/DOMAIN.md

- docs/DEVELOPMENT_RULES.md

A IA nunca deverá implementar funcionalidades fora da fase atual do Roadmap.

---

# Contribuição

Todo desenvolvimento deverá seguir:

- commits pequenos;

- código limpo;

- documentação atualizada;

- testes automatizados.

---

# Licença

Copyright © JMF System.

Todos os direitos reservados.

Este projeto encontra-se em desenvolvimento privado.