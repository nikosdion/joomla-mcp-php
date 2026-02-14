# Stage 1: Install production Composer dependencies
FROM composer:2 AS deps

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

# Stage 2: Runtime image
FROM php:8.4-cli-alpine

# Install pcntl for graceful signal handling in StdioServerTransport
RUN docker-php-ext-install pcntl

# Create non-root user
RUN adduser -D -h /app mcp4joomla

WORKDIR /app

# Copy application files
COPY mcp4joomla.php version.php ./
COPY src/ src/
COPY --from=deps /build/vendor/ vendor/

# Create log directory owned by the non-root user
RUN mkdir -p /app/log && chown mcp4joomla:mcp4joomla /app/log

USER mcp4joomla

ENTRYPOINT ["php", "/app/mcp4joomla.php"]
CMD ["server"]
