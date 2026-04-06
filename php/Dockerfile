FROM php:8.2-fpm

# 設定工作目錄
WORKDIR /var/www/html

# 1. 安裝系統依賴
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    locales \
    zip \
    unzip \
    git \
    curl \
    vim \
    gnupg2 \
    apt-transport-https

# 2. 安裝 Microsoft ODBC Driver 17 (預設不強制加密)
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft-archive-keyring.gpg \
    && echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-archive-keyring.gpg] https://packages.microsoft.com/debian/11/prod bullseye main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql17 unixodbc-dev

# 3. 安裝 PHP 核心擴充
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# 4. 使用 PECL 安裝 MSSQL 擴充功能 (使用相容 Driver 17 的版本)
RUN pecl channel-update pecl.php.net \
    && pecl install sqlsrv-5.10.1 pdo_sqlsrv-5.10.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# 5. 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. 處理權限
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 清理快取
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

EXPOSE 9000
CMD ["php-fpm"]