/**
 * Type definitions for SyncAwsSecretsAndParameters action
 */

/**
 * Mapping configuration that defines how to fetch a value from AWS
 * and where to place it in the PHP config file
 */
export interface ConfigMapping {
  /** Path within the PHP config array (e.g., ['db', 'host']) */
  configPath: string[];

  /** AWS resource path (supports placeholder substitution) */
  awsPath: string;

  /** Type of AWS resource */
  type: "parameter" | "secret";

  /** For JSON secrets, the key to extract from the JSON object */
  secretKey?: string;

  /** Optional string to prepend to the fetched value */
  prepend?: string;

  /** Optional string to append to the fetched value */
  append?: string;
}

/**
 * Placeholder definition for dynamic value substitution
 */
export interface Placeholder {
  /** Placeholder name (e.g., 'ENV', 'env') */
  key: string;

  /** Template expression (e.g., '${environment.toUpperCase()}') */
  value: string;
}

/**
 * Configuration for a single PHP config file
 */
export interface ConfigFile {
  /** Relative path to the PHP config file */
  path: string;

  /** Array of mappings to apply to this file */
  mappings: ConfigMapping[];
}

/**
 * Service-level configuration
 */
export interface ServiceConfig {
  /** Service identifier (e.g., 'api', 'selfserve') */
  service: string;

  /** Base path relative to project root */
  basePath: string;

  /** Placeholder definitions for this service */
  placeholders: Placeholder[];

  /** Configuration files to process */
  files: ConfigFile[];
}

/**
 * Array of service configurations (nullable for loading state)
 */
export type ServiceConfigs = ServiceConfig[] | null | undefined;
