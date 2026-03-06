# Build stage
FROM debian:trixie-slim AS builder

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    php8.4-cli \
    php8.4-curl \
    php8.4-gd \
    php8.4-intl \
    php8.4-zip \
    php8.4-mbstring \
    php8.4-opcache \
    php8.4-pdo \
    php8.4-pgsql \
    php8.4-xml \
    php8.4-redis \
    gettext \
    ca-certificates \
    libicu-dev \
    openssl \
    unzip \
    git \
    curl && \
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

# Copy full source
COPY . .

# Install PHP dependencies (composer)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --optimize-autoloader

# Install Node dependencies (npm)
COPY package.json package-lock.json* ./
RUN npm install

# Run frontend build
RUN npm run build

# Fix permissions
RUN chmod -R 775 /app/var /app/public

# Production image
FROM debian:trixie-slim AS prod

ENV TZ=Europe/Zagreb
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y --no-install-recommends \
    php8.4-fpm \
    php8.4-curl \
    php8.4-gd \
    php8.4-intl \
    php8.4-zip \
    php8.4-mbstring \
    php8.4-opcache \
    php8.4-pdo \
    php8.4-pgsql \
    php8.4-xml \
    php8.4-redis \
    gettext \
    openssl \
    libicu-dev \
    ca-certificates \
    nginx supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy application source
COPY --from=builder /app /app

# Copy configuration files
COPY docker/config/php/php-fpm.conf /etc/php/8.4/fpm/php-fpm.conf
COPY docker/config/php/pool.conf /etc/php/8.4/fpm/pool.d/www.conf
COPY docker/config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/config/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/config/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/build/container-start.sh /docker-entrypoint.sh

EXPOSE 8080

USER 1000:0

STOPSIGNAL SIGQUIT

CMD ["/bin/bash", "/docker-entrypoint.sh"]

# Development image
FROM prod AS dev

COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

USER 0:0

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    default-mysql-client \
    openssh-client \
    iputils-ping \
    telnet \
    git \
    wget \
    curl \
    php8.4-xdebug \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://get.symfony.com/cli/installer | bash - && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

RUN echo "zend_extension=xdebug.so" > /etc/php/8.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.mode=coverage" >> /etc/php/8.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.start_with_request=1" >> /etc/php/8.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.discover_client_host=true" >> /etc/php/8.4/cli/conf.d/20-xdebug.ini

RUN useradd --create-home --shell /bin/bash --groups root,video --uid 1000 app

USER app

EXPOSE 8080 5173

CMD ["/usr/bin/supervisord"]

# Production image is the default if no specific target is specified
FROM prod
