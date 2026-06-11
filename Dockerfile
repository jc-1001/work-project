FROM php:8.2-fpm-bullseye

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    curl \
    gnupg2 \
    apt-transport-https \
    unixodbc \
    unixodbc-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Microsoft ODBC Driver for SQL Server (required for sqlsrv PHP extension)
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/11/prod bullseye main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions: MSSQL + Redis + Laravel common
# sqlsrv 5.11.1 is the last version supporting PHP 8.1
RUN pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 redis \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv redis \
    && docker-php-ext-install zip bcmath \
    && docker-php-ext-enable opcache

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN usermod -u 1000 www-data && \
    mkdir -p /var/log/supervisor && \
    rm -rf /etc/nginx/conf.d/*

COPY --chown=www-data ./docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod 700 /docker-entrypoint.sh

CMD ["sh", "-c", "/docker-entrypoint.sh"]
