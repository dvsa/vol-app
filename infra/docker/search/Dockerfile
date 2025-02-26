FROM ghcr.io/dvsa/dvsa-docker-images/logstash/8.16.0/batch:0 AS search

FROM search AS production

USER root

RUN /usr/share/logstash/bin/logstash-plugin install logstash-integration-jdbc

# copy lib database jdbc jars
COPY mysql-connector-j-9.2.0.jar /usr/share/logstash/logstash-core/lib/jars/mysql-connector-java.jar

# Delete default configuration
RUN rm -f /usr/share/logstash/pipeline/logstash.conf

# copy configuration
COPY config/ /usr/share/logstash/config/
COPY entrypoint.sh /usr/share/logstash/entrypoint.sh
COPY build.sh /usr/share/logstash/build.sh

RUN chown -R logstash:root /usr/share/logstash

RUN chmod -R 755 /usr/share/logstash/*

USER logstash

CMD ["/usr/share/logstash/entrypoint.sh"]
