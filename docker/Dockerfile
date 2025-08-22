FROM php:8.3-fpm-alpine

# 安装系统依赖
RUN apk --no-cache add \
    nginx \
    supervisor \
    && docker-php-ext-enable opcache

# 安装PHP扩展
RUN docker-php-ext-install pdo_mysql pdo_sqlite opcache

# 创建应用目录
WORKDIR /var/www/mcp

# 复制应用代码
COPY . /var/www/mcp
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/production.ini

# 安装Composer依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 设置权限
RUN chown -R www-data:www-data /var/www/mcp

# 暴露端口
EXPOSE 80

# 启动supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
