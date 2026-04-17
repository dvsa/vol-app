/**
 * Config loader with runtime validation
 * Class-based approach to avoid module initialization issues
 */
import fs from "fs";
import path from "path";
import createDebug from "debug";
import type { ServiceConfig } from "./types";

const debug = createDebug("refresh:actions:SyncAwsSecretsAndParameters:config");

// Module-level debug
debug("config.ts module loading...");

class ConfigValidationError extends Error {
  constructor(
    message: string,
    public path?: string,
  ) {
    super(path ? `${message} at ${path}` : message);
    this.name = "ConfigValidationError";
  }
}

/**
 * ConfigLoader - handles loading and validation of config.json
 */
export class ConfigLoader {
  private static cachedConfig: ServiceConfig[] | null = null;

  /**
   * Get the validated configuration (cached after first load)
   */
  static getConfig(): ServiceConfig[] {
    if (!ConfigLoader.cachedConfig) {
      debug("Loading config for the first time...");
      ConfigLoader.cachedConfig = ConfigLoader.loadConfig();
      debug(
        "Loaded config: type=%s, isArray=%s, length=%d",
        typeof ConfigLoader.cachedConfig,
        Array.isArray(ConfigLoader.cachedConfig),
        ConfigLoader.cachedConfig?.length,
      );
    }
    return ConfigLoader.cachedConfig;
  }

  /**
   * Reset the cached config (useful for testing)
   */
  static resetCache(): void {
    ConfigLoader.cachedConfig = null;
  }

  /**
   * Load and validate mappings.json
   */
  private static loadConfig(): ServiceConfig[] {
    const configPath = path.join(__dirname || path.dirname(__filename), "mappings.json");

    try {
      const rawContent = fs.readFileSync(configPath, "utf-8");
      const parsed = JSON.parse(rawContent);

      ConfigLoader.validateConfig(parsed);

      return parsed;
    } catch (error) {
      if (error instanceof ConfigValidationError) {
        throw new Error(`Configuration validation failed: ${error.message}`);
      }
      if (error instanceof SyntaxError) {
        throw new Error(`Invalid JSON in config.json: ${error.message}`);
      }
      throw error;
    }
  }

  /**
   * Validates the entire config structure
   */
  private static validateConfig(config: unknown): asserts config is ServiceConfig[] {
    if (!Array.isArray(config)) {
      throw new ConfigValidationError("Config must be an array");
    }

    if (config.length === 0) {
      throw new ConfigValidationError("Config must contain at least one service");
    }

    config.forEach((service, i) => {
      ConfigLoader.isValidServiceConfig(service, i);
    });
  }

  /**
   * Validates a service config object
   */
  private static isValidServiceConfig(service: any, index: number): boolean {
    const path = `config[${index}]`;

    if (typeof service !== "object" || service === null) {
      throw new ConfigValidationError("ServiceConfig must be an object", path);
    }

    if (typeof service.service !== "string" || !service.service.trim()) {
      throw new ConfigValidationError("service must be a non-empty string", `${path}.service`);
    }

    if (typeof service.basePath !== "string" || !service.basePath.trim()) {
      throw new ConfigValidationError("basePath must be a non-empty string", `${path}.basePath`);
    }

    if (!Array.isArray(service.placeholders)) {
      throw new ConfigValidationError("placeholders must be an array", `${path}.placeholders`);
    }

    service.placeholders.forEach((placeholder: any, i: number) => {
      ConfigLoader.isValidPlaceholder(placeholder, `${path}.placeholders[${i}]`);
    });

    if (!Array.isArray(service.files) || service.files.length === 0) {
      throw new ConfigValidationError("files must be a non-empty array", `${path}.files`);
    }

    service.files.forEach((file: any, i: number) => {
      ConfigLoader.isValidConfigFile(file, `${path}.files[${i}]`);
    });

    return true;
  }

  /**
   * Validates a config file object
   */
  private static isValidConfigFile(file: any, path: string): boolean {
    if (typeof file !== "object" || file === null) {
      throw new ConfigValidationError("ConfigFile must be an object", path);
    }

    if (typeof file.path !== "string" || !file.path.trim()) {
      throw new ConfigValidationError("path must be a non-empty string", `${path}.path`);
    }

    if (!Array.isArray(file.mappings)) {
      throw new ConfigValidationError("mappings must be an array", `${path}.mappings`);
    }

    file.mappings.forEach((mapping: any, i: number) => {
      ConfigLoader.isValidMapping(mapping, `${path}.mappings[${i}]`);
    });

    return true;
  }

  /**
   * Validates a config mapping object
   */
  private static isValidMapping(mapping: any, path: string): boolean {
    if (typeof mapping !== "object" || mapping === null) {
      throw new ConfigValidationError("Mapping must be an object", path);
    }

    if (!Array.isArray(mapping.configPath) || mapping.configPath.length === 0) {
      throw new ConfigValidationError("configPath must be a non-empty array", `${path}.configPath`);
    }

    if (!mapping.configPath.every((p: any) => typeof p === "string" && p.trim())) {
      throw new ConfigValidationError("configPath must contain only non-empty strings", `${path}.configPath`);
    }

    if (typeof mapping.awsPath !== "string" || !mapping.awsPath.trim()) {
      throw new ConfigValidationError("awsPath must be a non-empty string", `${path}.awsPath`);
    }

    if (mapping.type !== "parameter" && mapping.type !== "secret") {
      throw new ConfigValidationError('type must be "parameter" or "secret"', `${path}.type`);
    }

    if (mapping.secretKey !== undefined && typeof mapping.secretKey !== "string") {
      throw new ConfigValidationError("secretKey must be a string", `${path}.secretKey`);
    }

    if (mapping.prepend !== undefined && typeof mapping.prepend !== "string") {
      throw new ConfigValidationError("prepend must be a string", `${path}.prepend`);
    }

    if (mapping.append !== undefined && typeof mapping.append !== "string") {
      throw new ConfigValidationError("append must be a string", `${path}.append`);
    }

    return true;
  }

  /**
   * Validates a placeholder object
   */
  private static isValidPlaceholder(placeholder: any, path: string): boolean {
    if (typeof placeholder !== "object" || placeholder === null) {
      throw new ConfigValidationError("Placeholder must be an object", path);
    }

    if (typeof placeholder.key !== "string" || !placeholder.key.trim()) {
      throw new ConfigValidationError("Placeholder.key must be a non-empty string", `${path}.key`);
    }

    if (typeof placeholder.value !== "string" || !placeholder.value.trim()) {
      throw new ConfigValidationError("Placeholder.value must be a non-empty string", `${path}.value`);
    }

    return true;
  }
}
