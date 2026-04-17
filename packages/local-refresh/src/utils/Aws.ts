import { GetSecretValueCommand, SecretsManagerClient } from "@aws-sdk/client-secrets-manager";
import { GetParameterCommand, SSMClient } from "@aws-sdk/client-ssm";
import { GetCallerIdentityCommand, STSClient } from "@aws-sdk/client-sts";
import { fromEnv } from "@aws-sdk/credential-providers";
import createDebug from "debug";

/**
 * Type for cache result objects (used for both parameter and secret caches).
 */
export type AwsCacheResult = { value: string | null; error?: string };
export type AwsCacheType = "parameter" | "secret";

/**
 * Enum for cache types.
 */
export enum CacheType {
  PARAMETER = "parameter",
  SECRET = "secret",
  ALL = "all",
}

/**
 * Represents the value and error for parameters.
 */
export interface AwsParameter {
  value: string | null;
  error?: string;
}

/**
 * Represents the value and error for secrets.
 */
export interface AwsSecret {
  value: string | null;
  error?: string;
  parsed?: Record<string, any>; // Cached parsed JSON object (supports nested objects, arrays, etc.)
}

/**
 * Represents the value and error for JSON key lookups in secrets.
 */
export interface AwsSecretValue {
  value: string | null;
  error?: string;
}

const debug = createDebug("refresh:actions:SyncAwsSecretsAndParameters:awsUtils");

/**
 * AWS utility class for secrets/parameters with internal caching and optional custom clients/credentials.
 */
export class Aws {
  private parameterCache = new Map<string, AwsParameter>();
  private secretCache = new Map<string, AwsSecret>();
  private readonly region: string;
  private readonly credentials: any;
  private readonly ssmClient: SSMClient;
  private readonly secretsClient: SecretsManagerClient;
  private readonly stsClient: STSClient;

  /**
   * Construct Aws with optional clients/credentials/region.
   */
  constructor(options?: {
    region?: string;
    credentials?: any;
    ssmClient?: SSMClient;
    secretsClient?: SecretsManagerClient;
    stsClient?: STSClient;
  }) {
    this.region = options?.region || process.env.AWS_REGION || process.env.AWS_DEFAULT_REGION || "eu-west-1";
    this.credentials = options?.credentials || fromEnv();
    this.ssmClient = options?.ssmClient || new SSMClient({ region: this.region, credentials: this.credentials });
    this.secretsClient =
      options?.secretsClient || new SecretsManagerClient({ region: this.region, credentials: this.credentials });
    this.stsClient = options?.stsClient || new STSClient({ region: this.region, credentials: this.credentials });
  }

  /**
   * Returns the AWS region for this instance.
   */
  getAwsRegion(): string {
    return this.region;
  }

  /**
   * Validates AWS credentials and returns identity info. Throws on error.
   */
  async validateAwsCredentials(): Promise<{ arn: string; account: string; region: string }> {
    debug("Validating AWS credentials...");
    try {
      const command = new GetCallerIdentityCommand({});
      const response = await this.stsClient.send(command);
      return {
        arn: response.Arn || "",
        account: response.Account || "",
        region: this.region,
      };
    } catch (error: any) {
      if (error.name === "ExpiredTokenException") {
        throw new Error("AWS session has expired. Please refresh your credentials and try again.");
      }
      throw new Error(`AWS credential validation failed: ${error.message || error.toString()}`);
    }
  }

  /**
   * Parse AWS SDK error into a user-friendly message
   */
  private parseAwsError(error: any, resourceType: "parameter" | "secret"): string {
    // Check for common AWS error types
    if (error.name === "ParameterNotFound" || error.$metadata?.httpStatusCode === 400) {
      return `${resourceType === "parameter" ? "Parameter" : "Secret"} does not exist`;
    }
    if (error.name === "ResourceNotFoundException") {
      return `${resourceType === "parameter" ? "Parameter" : "Secret"} does not exist`;
    }
    if (error.name === "AccessDeniedException") {
      return "Access denied - check IAM permissions";
    }
    if (error.name === "InvalidParameterException") {
      return "Invalid parameter name or format";
    }

    // Return the error message or a generic message
    return error.message || error.toString() || "Unknown error";
  }

  /**
   * Gets a parameter value from AWS SSM, with internal caching.
   */
  async getParameter(name: string): Promise<AwsParameter> {
    debug(`getParameter: ${name}`);
    const cacheKey = `${name}`;
    if (this.parameterCache.has(cacheKey)) return this.parameterCache.get(cacheKey)!;
    try {
      const paramCommand = new GetParameterCommand({ Name: name, WithDecryption: true });
      const paramResult = await this.ssmClient.send(paramCommand);
      const result: AwsParameter = { value: paramResult.Parameter?.Value || null };
      this.parameterCache.set(cacheKey, result);
      return result;
    } catch (error: any) {
      const errorMessage = this.parseAwsError(error, "parameter");
      const result: AwsParameter = { value: null, error: errorMessage };
      this.parameterCache.set(cacheKey, result);
      return result;
    }
  }

  /**
   * Gets a secret string from AWS SecretsManager, with internal caching.
   * Automatically parses JSON secrets and caches the parsed object.
   */
  async getSecret(secretId: string): Promise<AwsSecret> {
    debug(`getSecret: ${secretId}`);
    const cacheKey = `${secretId}`;
    if (this.secretCache.has(cacheKey)) return this.secretCache.get(cacheKey)!;
    try {
      const secretCommand = new GetSecretValueCommand({ SecretId: secretId });
      const secretResult = await this.secretsClient.send(secretCommand);
      const secretString = secretResult.SecretString || null;

      const result: AwsSecret = { value: secretString };

      // Try to parse as JSON and cache the parsed object
      if (secretString) {
        try {
          result.parsed = JSON.parse(secretString);
          debug(`Parsed secret as JSON: ${secretId}`);
        } catch {
          // Not JSON or malformed, that's okay - keep parsed undefined
          debug(`Secret is not valid JSON: ${secretId}`);
        }
      }

      this.secretCache.set(cacheKey, result);
      return result;
    } catch (error: any) {
      const errorMessage = this.parseAwsError(error, "secret");
      const result: AwsSecret = { value: null, error: errorMessage };
      this.secretCache.set(cacheKey, result);
      return result;
    }
  }

  /**
   * Gets a value from a JSON secret in AWS SecretsManager, with internal caching.
   * Uses the cached parsed JSON from getSecret for efficiency (no re-parsing).
   */
  async getSecretValue(secretId: string, key: string): Promise<AwsSecretValue> {
    debug(`getSecretValue: ${secretId}, ${key}`);

    // Use getSecret to leverage its cache (which includes parsed JSON)
    const secretResult = await this.getSecret(secretId);

    if (!secretResult.value) {
      return { value: null, error: secretResult.error || "Secret string not found" };
    }

    // Check if we have a cached parsed JSON object
    if (!secretResult.parsed) {
      return { value: null, error: "Secret is not valid JSON" };
    }

    // Get the value from the cached parsed JSON (already parsed, no JSON.parse needed!)
    const value = secretResult.parsed[key];

    // Check if the key exists in the JSON (could be null, undefined, or missing)
    if (value === undefined || value === null) {
      return { value: null, error: `Key '${key}' not found in secret JSON` };
    }

    // Handle arrays and objects by converting to JSON string
    if (typeof value === "object") {
      return { value: JSON.stringify(value) };
    }

    // Convert primitives to string for consistency
    return { value: String(value) };
  }

  /**
   * Clears the internal AWS caches.
   * @param type Which cache(s) to clear: CacheType.ALL (default), CacheType.PARAMETER, CacheType.SECRET, or an array of these.
   */
  clearCache(type: CacheType | CacheType[] = CacheType.ALL) {
    switch (type) {
      case CacheType.ALL:
        this.parameterCache.clear();
        this.secretCache.clear();
        break;
      case CacheType.PARAMETER:
        this.parameterCache.clear();
        break;
      case CacheType.SECRET:
        this.secretCache.clear();
        break;
      default:
        if (Array.isArray(type)) {
          if (type.includes(CacheType.PARAMETER)) {
            this.parameterCache.clear();
          }
          if (type.includes(CacheType.SECRET)) {
            this.secretCache.clear();
          }
        }
        break;
    }
  }

  /**
   * Get cache statistics for monitoring
   */
  getCacheStats(): { parameterCount: number; secretCount: number } {
    return {
      parameterCount: this.parameterCache.size,
      secretCount: this.secretCache.size,
    };
  }
}
