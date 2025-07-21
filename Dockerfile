FROM php:8.1-apache

# mod_rewriteを有効化
RUN a2enmod rewrite

# PHP拡張モジュールのインストール（mysqli, PDO系）
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apacheのドキュメントルートを明示設定
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Apache設定の書き換え
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/../!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composerのインストール
RUN apt-get update && \
    apt-get install -y unzip zip curl git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# 作業ディレクトリ設定
WORKDIR /var/www/html

# ソースコードをコピー
COPY . /var/www/html

# Composer依存パッケージのインストール（dotenvなど）
RUN composer install
