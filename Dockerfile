# Dockerfile for Render / Railway / any Docker host.
# Uses the official PHP image and serves the app with the built-in server.

FROM php:8.3-cli

# System deps: curl, unzip
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        unzip \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

RUN chmod +x start.sh

EXPOSE 8000

CMD ["bash", "start.sh"]
