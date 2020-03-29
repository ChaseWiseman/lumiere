FROM wordpress:latest

ADD apache2-foreground-with-ssl /usr/local/bin/

RUN ["chmod", "+x", "/usr/local/bin/apache2-foreground-with-ssl"]

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["apache2-foreground-with-ssl"]
