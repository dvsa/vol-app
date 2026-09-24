FROM liquibase/liquibase:5.0.2
ARG ETL_SHA=unknown
LABEL org.opencontainers.image.revision=$ETL_SHA
USER root
COPY changelog /liquibase/changelog
COPY entrypoint.sh /liquibase/
RUN chmod +x /liquibase/entrypoint.sh && echo "$ETL_SHA" > /liquibase/ETL_SHA
ENV INSTALL_MYSQL=true
USER liquibase
ENTRYPOINT ["/liquibase/entrypoint.sh"]
