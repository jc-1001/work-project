ARG REGISTRY=registry.e-gps.tw
FROM ${REGISTRY}/dev/lnp:php8.1-nginx1.24-bullseye

WORKDIR /var/www/html

RUN usermod -u 1000 www-data
# COPY --chown=www-data ./ /var/www/html
COPY --chown=www-data ./docker-entrypoint.sh /docker-entrypoint.sh

# Make sure files/folders needed by the processes are accessable when they run under the www-data user
RUN chown -R www-data.www-data /run && \
    chown -R www-data.www-data /var/cache/nginx && \
    chown -R www-data.www-data /usr/lib/nginx && \
    chown -R www-data.www-data /var/log/nginx && \
    chown -R www-data.www-data /var/log/supervisor && \
    rm -rf /etc/nginx/conf.d/*

RUN chmod 700 /docker-entrypoint.sh

USER www-data

CMD ["sh", "-c", "/docker-entrypoint.sh"]
