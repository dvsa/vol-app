FROM liquibase/liquibase:4.30
USER root
COPY changelog /liquibase/changelog
COPY entrypoint.sh /liquibase/
RUN chmod +x /liquibase/entrypoint.sh
ENV INSTALL_MYSQL=true
USER liquibase
ENTRYPOINT ["/liquibase/entrypoint.sh"]
