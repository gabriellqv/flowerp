FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Limpa cache do apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . /var/www/html

# Ajusta permissões (útil se as pastas de cache e logs já existirem)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
