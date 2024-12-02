FROM alpine/git AS repo
WORKDIR /app
RUN git clone https://github.com/dvsa/olcs-etl .

FROM liquibase/liquibase

RUN apt-get update && apt-get install -y \
    aws-cli \
    jq \
    && rm -rf /var/lib/apt/lists/*

COPY --from=repo /app /liquibase/changelog

COPY entrypoint.sh /liquibase/
RUN chmod +x /liquibase/entrypoint.sh

ENV INSTALL_MYSQL=true

ENTRYPOINT ["/liquibase/entrypoint.sh"]
