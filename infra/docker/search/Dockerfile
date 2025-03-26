FROM ghcr.io/dvsa/dvsa-docker-images/logstash/8.16.0/batch:0 AS search

FROM search AS production

USER root

RUN apt-get update && apt-get install --no-install-recommends -y python3=3.* && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    /usr/share/logstash/bin/logstash-plugin install logstash-integration-jdbc

# copy lib database jdbc jars
COPY mysql-connector-j-9.2.0.jar /usr/share/logstash/logstash-core/lib/jars/mysql-connector-java.jar

# Delete default configuration
RUN rm -f /usr/share/logstash/pipeline/logstash.conf

# Copy configuration
COPY config/ /usr/share/logstash/config/

# copy scripts
COPY py /usr/share/logstash/py
COPY build.sh /usr/share/logstash/build.sh

COPY entrypoint.sh /usr/share/logstash/entrypoint.sh

RUN chown -R logstash:logstash /usr/share/logstash/* && \
    chmod -R 755 /usr/share/logstash/* && \
    chmod +x /usr/share/logstash/entrypoint.sh

USER logstash

ENTRYPOINT ["sh", "/usr/share/logstash/entrypoint.sh"]

#Deploy 26-03-2025
