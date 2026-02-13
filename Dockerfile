FROM php:8.1-cli

WORKDIR /app

COPY . .

RUN mkdir -p data/tokens data/instances data/rate_limits && \
    chmod -R 777 data

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "."]
