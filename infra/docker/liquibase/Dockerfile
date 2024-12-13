FROM alpine/git AS repo
ARG GITHUB_TOKEN
WORKDIR /app
RUN git clone https://${GITHUB_TOKEN}@github.com/dvsa/olcs-etl .

FROM liquibase/liquibase
USER root
COPY --from=repo /app /liquibase/changelog
COPY entrypoint.sh /liquibase/
RUN chmod +x /liquibase/entrypoint.sh
ENV INSTALL_MYSQL=true
USER liquibase
ENTRYPOINT ["/liquibase/entrypoint.sh"]
