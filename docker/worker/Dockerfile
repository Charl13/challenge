FROM challenge-app:latest

RUN apk --update add supervisor

RUN mkdir -p /etc/supervisor.d

COPY docker/worker/supervisord.conf /etc/supervisord.conf
COPY docker/worker/worker.conf /etc/supervisor.d/worker.conf

CMD supervisord -n
