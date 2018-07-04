FROM php:7.2.3-stretch

RUN curl -L http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o php-cs-fixer \
  && mv php-cs-fixer /usr/local/bin/php-cs-fixer \
  && chmod +x /usr/local/bin/php-cs-fixer

ENTRYPOINT ["php-cs-fixer"]

WORKDIR "/app"
VOLUME ["/app"]