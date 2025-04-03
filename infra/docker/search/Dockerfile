FROM ghcr.io/dvsa/dvsa-docker-images/logstash/8.16.0/batch:0 AS search

FROM search AS production

USER root

RUN apt-get update && apt-get install --no-install-recommends -y python3=3.* && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    /usr/share/logstash/bin/logstash-plugin install logstash-integration-jdbc && \
    /usr/share/logstash/bin/logstash-plugin install logstash-output-opensearch

USER logstash

# copy lib database jdbc jars
COPY --chown=logstash --chmod=755 mysql-connector-j-9.2.0.jar /usr/share/logstash/logstash-core/lib/jars/mysql-connector-java.jar

# Delete default configuration
RUN rm -f /usr/share/logstash/pipeline/logstash.conf

# Copy configuration
COPY --chown=logstash --chmod=755 config/ /usr/share/logstash/config/

# copy scripts
COPY --chown=logstash --chmod=755 py /usr/share/logstash/py
COPY --chown=logstash --chmod=755 build.sh /usr/share/logstash/build.sh

COPY --chown=logstash --chmod=755 entrypoint.sh /usr/local/bin/docker-entrypoint
