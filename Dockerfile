FROM vinayrev/composer:1.0.0

RUN composer global require "phpunit/phpunit=4.8.*"
RUN composer global require "phpunit/php-invoker=~1.1."

ADD . /app

ENTRYPOINT "/app/entrypoint.sh"
