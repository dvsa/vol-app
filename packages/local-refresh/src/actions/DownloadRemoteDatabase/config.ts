import type { RemoteDatabaseConfig, LocalDatabaseConfig } from "./types";

/**
 * Remote database configurations for DEV and INT environments.
 * Passwords are fetched from AWS Secrets Manager at runtime.
 */
export const REMOTE_DATABASES: Record<string, RemoteDatabaseConfig> = {
  dev: {
    name: "dev",
    displayName: "DEV",
    host: "olcsreaddb-rds.dev.olcs.dev-dvsacloud.uk",
    port: 3306,
    user: "olcsapi",
    database: "OLCS_RDS_OLCSDB",
    awsSecretPath: "DEVAPPDEV-BASE-SM-APPLICATION-API",
  },
  int: {
    name: "int",
    displayName: "INT (QA)",
    host: "olcsreaddb-rds.qa.olcs.dev-dvsacloud.uk",
    port: 3306,
    user: "olcsapi",
    database: "OLCS_RDS_OLCSDB",
    awsSecretPath: "DEVAPPQA-BASE-SM-APPLICATION-API",
  },
};

/**
 * Local database configuration (from compose.yaml).
 */
export const LOCAL_DATABASE: LocalDatabaseConfig = {
  user: "root",
  password: "olcs",
  database: "olcs_be",
  dockerContainer: "db",
};

/**
 * Key name for the password field in AWS Secrets Manager.
 */
export const AWS_SECRET_PASSWORD_KEY = "olcs_api_rds_password";
