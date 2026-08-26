/**
 * Configuration for a remote database connection.
 */
export interface RemoteDatabaseConfig {
  name: string;
  displayName: string;
  host: string;
  port: number;
  user: string;
  database: string;
  awsSecretPath: string;
}

/**
 * Configuration for the local database connection.
 */
export interface LocalDatabaseConfig {
  user: string;
  password: string;
  database: string;
  dockerContainer: string;
}

/**
 * Options for filtering which tables to include in the download.
 */
export interface TableFilterOptions {
  excludeQueueTables: boolean;
  excludeHistTables: boolean;
  excludeReadAuditTables: boolean;
  queueDateFilter?: Date;
}

/**
 * Complete options for the download operation.
 */
export interface DownloadOptions {
  environment: "dev" | "int";
  tableFilters: TableFilterOptions;
  backupFirst: boolean;
  /** Path to existing dump file to import instead of downloading */
  existingDumpFile?: string;
}
