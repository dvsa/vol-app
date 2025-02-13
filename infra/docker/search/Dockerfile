FROM ghcr.io/dvsa/dvsa-docker-images/logstash/8.16.0/batch:0 AS search

FROM search AS production

# Delete default configuration
RUN rm -f /usr/share/logstash/pipeline/logstash.conf

COPY config/ /usr/share/logstash/config/

COPY entrypoint.sh /usr/share/logstash/entrypoint.sh
COPY build.sh /usr/share/logstash/build.sh

USER 1000

CMD ["/usr/share/logstash/entrypoint.sh"]
# build 2025-01-23-1536
