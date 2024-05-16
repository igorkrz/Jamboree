FROM php:8.2-fpm-bullseye as base

# Declare enviroment variables required to run image build process
ARG SSH_PRIVATE_KEY
RUN set -eu;
# Install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -sL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get --allow-releaseinfo-change-suite update && \
    apt-get install -y \
        acl \
        git \
        openssh-client \
        libpq-dev \
        libicu-dev \
        libzip-dev  \
        zip \
        libcurl4-openssl-dev \
        libpng-dev \
        nodejs \
        ssh \
        tar \
        wget \
        pdftk \
        --no-install-recommends && \
    apt-get clean -y && \
    docker-php-ext-configure intl && \
    docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pgsql pdo pdo_pgsql intl zip gd fileinfo exif opcache && \
    npm install -g yarn

# Copy php config files into container
COPY ./docker/php/config/php.ini /usr/local/etc/php/conf.d/php.ini
# NB: filenames other that www.conf are not effective on config merge on php-fpm start (edited)
COPY ./docker/php/config/www.conf /usr/local/etc/php-fpm.d/www.conf

# Authorize SSH Host
RUN mkdir -p /root/.ssh && \
    chmod 0700 /root/.ssh && \
    echo "${SSH_PRIVATE_KEY}" | base64 --decode > /root/.ssh/id_rsa && \
    chmod 600 /root/.ssh/id_rsa && \
    ssh-keyscan -t rsa github.com  >> /root/.ssh/known_hosts && \
    ssh-keyscan -t rsa bitbucket.org >> /root/.ssh/known_hosts

# Copy app files into container
COPY . /var/www
WORKDIR /var/www

# Install symfony cli tool for security check
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Setup entrypoint script
COPY ./docker/php/entrypoint.bash /entrypoint.bash
RUN chmod +x /entrypoint.bash
ENTRYPOINT ["/entrypoint.bash"]
CMD ["php-fpm"]

# Run prod env specific commands for our prod image
FROM base as prod-image

# Declare enviroment variables required to run app build process in the prod env
ARG XDEBUG_ENABLED="false"
ARG APP_ENV=prod
ARG APP_DEBUG=0

ENV PHP_TIMEZONE="Europe/Zagreb" \
    COMPOSER_ALLOW_SUPERUSER=1

RUN set -eu; \
    composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer symfony:dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync; \
    rm -rf /var/www/.composer

# Run dev env specific commands for our dev image
FROM base as dev-image

# Declare enviroment variables required to run app build process in the dev env
ARG XDEBUG_ENABLED="true"
ARG APP_ENV=dev
ARG APP_DEBUG=1

ENV PHP_TIMEZONE="Europe/Zagreb"

#RUN cat /etc/passwd
#RUN adduser 1000 1000 --disabled-password
#RUN adduser 1000 root
#RUN chown -R 1000.1000 /docker/*

# Add a user with a specific home directory
#RUN useradd -ms /bin/bash 1000

## Add the user to the sudo group (optional)
#RUN usermod -aG sudo 1000

#RUN chown -R 1000:1000 docker/*

# Install xdebug and enable it if env var XDEBUG_ENABLED is set to "true"
#RUN if [ "${XDEBUG_ENABLED}" = "true" ]; then \
##    pecl install xdebug-3.1.6; \
#    docker-php-ext-enable xdebug;  \
#    fi

#USER root

# Run composer
RUN composer install; \
    rm -rf /var/www/.composer

#USER 1000