# Instalação — JMF Customer Intelligence

## Requisitos

```
PHP 8.3.30 (cli)
Composer 2.9+
Node.js 22.12.0
npm 10.9+
MySQL 8
Laragon (Apache na porta 80, MySQL na porta 3306)
```

Extensões PHP necessárias (todas presentes por padrão no build do Laragon usado neste projeto): BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, PDO MySQL, Session, Tokenizer, XML.

## Passo a passo

1. Clonar/posicionar o projeto em:

   ```text
   D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE
   ```

2. Instalar dependências PHP:

   ```bash
   composer install
   ```

3. Copiar o arquivo de ambiente:

   ```bash
   copy .env.example .env
   ```

4. Gerar a chave da aplicação:

   ```bash
   php artisan key:generate
   ```

5. Configurar o `.env` com os dados do MySQL local (ver seção "Configuração do `.env`" no `README.md`).

6. Criar o banco de dados no MySQL:

   ```sql
   CREATE DATABASE jmf_customer_intelligence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

7. Rodar as migrations:

   ```bash
   php artisan migrate
   ```

8. Instalar dependências JavaScript:

   ```bash
   npm install
   ```

9. Compilar os assets:

   ```bash
   npm run build
   ```

10. Rodar os testes automatizados:

    ```bash
    php artisan test
    ```

## Configuração do Laragon (Virtual Host)

1. Garantir que o projeto está em `D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE`.
2. No Laragon, criar/editar o Virtual Host apontando o `DocumentRoot` para:

   ```text
   D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE\public
   ```

3. Domínio local sugerido: `jmf-customer-intelligence.test`.
4. Reiniciar o Apache pelo Laragon.
5. Acessar `http://jmf-customer-intelligence.test` no navegador.

## Ambiente de desenvolvimento

```bash
npm run dev
php artisan serve
```

Uso do `php artisan serve` é apenas para desenvolvimento rápido; a forma oficial de servir o projeto é via Apache/Laragon.

Processamento assíncrono local (fila em banco de dados, sem Redis/Horizon):

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

## Cron em produção (hospedagem compartilhada)

```cron
* * * * * php /caminho/do/projeto/artisan schedule:run >> /dev/null 2>&1
```
