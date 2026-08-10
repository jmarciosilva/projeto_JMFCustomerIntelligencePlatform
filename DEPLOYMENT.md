# Guia de Deployment — JMF Customer Intelligence

## Resumo

Este documento descreve como fazer deploy da aplicação em ambiente de produção, cobrindo configuração de servidor, database, variáveis de ambiente e manutenção contínua.

## Pré-requisitos

- VPS ou hospedagem Linux (recomendado Ubuntu 22.04 LTS ou CentOS 8+)
- PHP 8.3.30+ com extensões: `curl`, `json`, `bcmath`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 8.0+
- Composer 2.9+
- Git
- Nginx ou Apache 2.4+
- SSL/TLS Certificate (Let's Encrypt gratuito)

## Instalação do Servidor

### 1. Atualizar Sistema

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Instalar Dependências

```bash
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-curl php8.3-json \
  php8.3-bcmath php8.3-mysql php8.3-pdo php8.3-tokenizer php8.3-xml \
  php8.3-mbstring php8.3-gd php8.3-zip mysql-server mysql-client \
  nginx git curl wget unzip supervisor cron
```

### 3. Instalar Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

### 4. Instalar Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

## Configuração do Banco de Dados

### 1. Criar Database e Usuário

```bash
mysql -u root -p

CREATE DATABASE jmf_customer_intelligence;
CREATE USER 'jmf_ci'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON jmf_customer_intelligence.* TO 'jmf_ci'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Backup de Segurança

```bash
# Backup automático diário via cron
0 3 * * * mysqldump -u jmf_ci -psenha_segura jmf_customer_intelligence > /backups/jmf_ci_$(date +\%Y\%m\%d).sql
```

## Deploy da Aplicação

### 1. Clonar Repositório

```bash
cd /var/www
sudo git clone https://github.com/jmf-system/customer-intelligence.git jmf-ci-prod
cd jmf-ci-prod
sudo chown -R www-data:www-data .
```

### 2. Instalar Dependências PHP

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Instalar Dependências Frontend

```bash
npm install
npm run build
```

### 4. Configurar Variáveis de Ambiente

```bash
cp .env.example .env
# Editar .env com dados de produção
nano .env

# Geração de Application Key
php artisan key:generate
```

**Variáveis críticas para produção:**

```env
APP_NAME="JMF Customer Intelligence"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jmf-ci.seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jmf_customer_intelligence
DB_USERNAME=jmf_ci
DB_PASSWORD=senha_segura_aqui

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

# Email (se usando)
MAIL_MAILER=smtp
MAIL_HOST=smtp.seuservidor.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@exemplo.com
MAIL_PASSWORD=sua-senha
MAIL_FROM_NAME="JMF CI"

# Security
ALLOWED_DOMAINS=jmf-ci.seu-dominio.com

# Opcional: Geração de conteúdo via Anthropic
MARKETING_AI_DRIVER=template
# ANTHROPIC_API_KEY=sk-ant-... (deixar em branco para template)
```

### 5. Executar Migrations e Seeders

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan admin:create  # Ou usar ADMIN_EMAIL/ADMIN_PASSWORD no .env
```

### 6. Permissões de Arquivo

```bash
sudo chmod -R 755 /var/www/jmf-ci-prod
sudo chmod -R 777 /var/www/jmf-ci-prod/storage
sudo chmod -R 777 /var/www/jmf-ci-prod/bootstrap/cache
sudo chown -R www-data:www-data /var/www/jmf-ci-prod
```

## Configuração do Nginx

Criar arquivo `/etc/nginx/sites-available/jmf-ci-prod`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name jmf-ci.seu-dominio.com;

    # Redirecionar HTTP para HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name jmf-ci.seu-dominio.com;

    # SSL Certificate
    ssl_certificate /etc/letsencrypt/live/jmf-ci.seu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/jmf-ci.seu-dominio.com/privkey.pem;

    # Segurança SSL
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/jmf-ci-prod/public;
    index index.php index.html index.htm;

    client_max_body_size 100M;

    # Reescrever URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Negar acesso a arquivos sensíveis
    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/jmf-ci_access.log;
    error_log /var/log/nginx/jmf-ci_error.log;
}
```

Ativar site:

```bash
sudo ln -s /etc/nginx/sites-available/jmf-ci-prod /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Certificado SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot certonly --nginx -d jmf-ci.seu-dominio.com
# Renovação automática via cron (certbot já configura)
```

## Fila de Trabalhos (Supervisor)

Criar arquivo `/etc/supervisor/conf.d/jmf-ci.conf`:

```ini
[program:jmf-ci-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/jmf-ci-prod/artisan queue:work --tries=3 --delay=3 --timeout=90
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/jmf-ci-queue.log
user=www-data
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start jmf-ci-queue:*
```

## Scheduler (Cron)

Adicionar ao crontab do www-data:

```bash
sudo -u www-data crontab -e

# Adicionar linha:
* * * * * cd /var/www/jmf-ci-prod && php artisan schedule:run >> /dev/null 2>&1
```

Comandos agendados automáticos:
- `metrics:aggregate-daily` — 01:00 (agregação de métricas diárias)
- `intelligence:compute` — 02:00 (lead score)
- `intelligence:compute-segments` — 02:30 (segmentação)
- `intelligence:analyze-trends` — 03:00 (análise de tendências)
- `intelligence:detect-opportunities` — 03:30 (detecção de oportunidades)
- `intelligence:generate-recommendations` — 04:00 (recomendações de negócio)
- `trends:collect` — 05:00 (coleta de sinais de tendência)
- `trends:calculate-scores` — 05:30 (cálculo de trend scores)

## Monitoramento e Manutenção

### 1. Health Check

Endpoint público: `GET /` (retorna status 200 com informações da plataforma)

```bash
curl https://jmf-ci.seu-dominio.com/
```

### 2. Logs

```bash
# Logs do Laravel
tail -f /var/www/jmf-ci-prod/storage/logs/laravel.log

# Logs do Nginx
tail -f /var/log/nginx/jmf-ci_error.log

# Logs do Supervisor
tail -f /var/log/supervisor/jmf-ci-queue.log
```

### 3. Backup Automático

```bash
#!/bin/bash
# /usr/local/bin/backup-jmf-ci.sh

BACKUP_DIR="/backups/jmf-ci"
mkdir -p $BACKUP_DIR
DATE=$(date +%Y%m%d_%H%M%S)

# Database
mysqldump -u jmf_ci -psenha_segura jmf_customer_intelligence | \
  gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/jmf-ci-prod/storage/app

# Remover backups antigos (> 30 dias)
find $BACKUP_DIR -type f -mtime +30 -delete
```

Adicionar ao crontab:

```bash
0 4 * * * /usr/local/bin/backup-jmf-ci.sh
```

### 4. Monitoramento de Saúde

```bash
#!/bin/bash
# Script de verificação de saúde (executar a cada 5 min via cron)

HEALTH_URL="https://jmf-ci.seu-dominio.com/"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $HEALTH_URL)

if [ "$RESPONSE" != "200" ]; then
  echo "ALERTA: JMF CI respondendo com código $RESPONSE" | \
    mail -s "JMF CI Health Alert" admin@seu-email.com
  systemctl restart jmf-ci-queue:*
fi
```

## Atualização da Aplicação

```bash
cd /var/www/jmf-ci-prod
git fetch origin
git checkout main
git pull origin main

composer install --optimize-autoloader --no-dev
npm install
npm run build

php artisan down  # Modo manutenção
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan up  # Retornar ao ar
```

## Performance

### 1. Otimização de Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Otimização de Composer

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Otimização do MySQL

Adicionar a `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
max_connections=500
innodb_buffer_pool_size=50G
query_cache_size=64M
query_cache_type=1
```

### 4. Monitoramento de Recursos

```bash
# Instalar ferramentas
sudo apt install -y htop iotop nethogs

# Monitorar
htop  # CPU e memória
iotop  # I/O
nethogs  # Rede
```

## Troubleshooting

### A aplicação está lenta

1. Verificar logs: `tail -f storage/logs/laravel.log`
2. Verificar fila: `php artisan queue:failed`
3. Verificar cache: `php artisan cache:clear`
4. Verificar índices de database: `SHOW CREATE TABLE events\G;`

### Erros 500

1. Habilitar debug temporariamente: `APP_DEBUG=true` no `.env`
2. Verificar permissões: `ls -la storage/`
3. Verificar database connection
4. Desabilitar debug: `APP_DEBUG=false`

### Memória insuficiente

1. Aumentar `memory_limit` em `php.ini`: `memory_limit = 1024M`
2. Aumentar `innodb_buffer_pool_size` em MySQL
3. Reduzir número de processos PHP-FPM se necessário

## Rollback de Atualização

```bash
cd /var/www/jmf-ci-prod
git log --oneline  # Encontrar commit anterior
git checkout hash_anterior
php artisan migrate:rollback
php artisan cache:clear
```

## Política de Retenção de Dados

*(A ser definido antes da Fase 11 — Produção)*

- Eventos: Retenção de 2 anos
- Logs de Auditoria: Retenção de 1 ano
- Backups: Mínimo de 30 dias

## Checklist de Segurança em Produção

- [ ] APP_DEBUG = false
- [ ] APP_KEY = gerado e único
- [ ] HTTPS/SSL ativado
- [ ] Database isolada em sub-rede privada
- [ ] Firewall configurado (ufw ou iptables)
- [ ] Chaves SSH de acesso ao servidor
- [ ] Rate limiting nas APIs
- [ ] CORS configurado corretamente
- [ ] Logs de auditoria habilitados
- [ ] Backup automático testado
- [ ] Plano de disaster recovery documentado

## Suporte e Escalabilidade

Para aplicações com > 1M eventos/dia, considerar:
- Redis para cache/fila
- Elasticsearch para logs
- CDN para assets estáticos
- Database replication
- Load balancer

Consulte [`README.md`](README.md) para mais referências.

---

**Última atualização:** 2026-08-10
