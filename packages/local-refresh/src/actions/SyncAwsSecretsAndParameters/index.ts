import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import ActionInterface from "../ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import { fromEnv } from "@aws-sdk/credential-providers";
import { Aws } from "../../utils/Aws";
import { LaminasConfig } from "../../utils/LaminasConfig";
import type { ConfigMapping, ServiceConfig, ServiceConfigs } from "./types";

const debug = createDebug("refresh:actions:SyncAwsSecretsAndParameters");

export default class SyncAwsSecretsAndParameters implements ActionInterface {
  private selectedEnvironment: string = "";
  private loadedConfig: ServiceConfigs = null;
  private awsUtils: Aws | null = null;

  /**
   * Safely evaluate placeholder expressions like ${environment.toUpperCase()}
   */
  private evaluatePlaceholder(expression: string, context: Record<string, any>): string {
    // Find all ${...} expressions
    return expression.replace(/\$\{([^}]+)\}/g, (fullMatch, expr) => {
      const parts = expr.split(".");
      let value = context[parts[0]];

      if (value === undefined) {
        throw new Error(`Unknown placeholder variable: ${parts[0]}`);
      }

      // Apply transformations safely
      for (let i = 1; i < parts.length; i++) {
        const transform = parts[i];
        switch (transform) {
          case "toUpperCase()":
            value = value.toUpperCase();
            break;
          case "toLowerCase()":
            value = value.toLowerCase();
            break;
          default:
            throw new Error(`Unsupported transformation: ${transform}`);
        }
      }

      return value;
    });
  }

  /**
   * Resolve all placeholders in a service's configuration
   */
  private resolvePlaceholders(serviceConfig: any): Record<string, string> {
    const context = {
      environment: this.selectedEnvironment,
      service: serviceConfig.service,
    };

    // Resolve placeholder definitions
    const resolvedPlaceholders: Record<string, string> = {};

    // Placeholders is an array of {key, value} objects
    if (Array.isArray(serviceConfig.placeholders)) {
      for (const placeholder of serviceConfig.placeholders) {
        resolvedPlaceholders[placeholder.key] = this.evaluatePlaceholder(placeholder.value, context);
      }
    }

    return resolvedPlaceholders;
  }

  /**
   * Initialize and validate AWS utilities
   */
  private async initializeAwsUtils(credentials: any): Promise<void> {
    this.awsUtils = new Aws({ credentials });

    try {
      const identity = await this.awsUtils.validateAwsCredentials();
      console.log(chalk.blue(`✓ AWS credentials valid - User: ${identity.arn.split("/").pop()} (${identity.region})`));
      debug(`AWS Account: ${identity.account}, User ARN: ${identity.arn}, Region: ${identity.region}`);
    } catch (error: any) {
      throw error;
    }
  }

  async prompt(): Promise<boolean> {
    const { shouldSync } = await prompts({
      type: "confirm",
      name: "shouldSync",
      message: "Sync AWS secrets and parameters to local configuration files?",
      initial: false,
    });

    if (!shouldSync) {
      return false;
    }

    const { environment } = await prompts({
      type: "select",
      name: "environment",
      message: "Select environment for secrets:",
      choices: [
        { title: "DEV", value: "dev" },
        { title: "INT", value: "int" },
      ],
      initial: 0,
    });

    if (!environment) {
      return false;
    }

    this.selectedEnvironment = environment;

    // Initialize AWS utilities and validate credentials early
    const credentials = fromEnv();
    try {
      await this.initializeAwsUtils(credentials);
    } catch (error: any) {
      console.error(chalk.red(`❌ ${error.message}`));
      return false;
    }

    // Use validated configuration and count files that exist
    const { ConfigLoader } = await import("./config");
    const config = ConfigLoader.getConfig();

    let totalFilesToProcess = 0;

    // Loop through configured services to check file existence and count
    for (const serviceConfig of config) {
      const serviceName = serviceConfig.service;

      // Use basePath from config
      if (!serviceConfig.basePath) {
        debug(`No basePath defined for service: ${serviceName}`);
        continue;
      }
      const serviceDir = path.resolve(__dirname, `../../../../../${serviceConfig.basePath}`);

      if (!serviceDir || !fs.existsSync(serviceDir)) {
        debug(`No directory found for service: ${serviceName} (${serviceDir})`);
        continue;
      }

      // Check each configured file for this service
      serviceConfig.files.forEach((fileConfig: any) => {
        const relativePath = fileConfig.path;
        const fullPath = path.join(serviceDir, relativePath);

        if (fs.existsSync(fullPath)) {
          totalFilesToProcess++;
          debug(`Found config file: ${fullPath}`);
        } else {
          debug(`Config file not found: ${fullPath}`);
        }
      });
    }

    if (totalFilesToProcess === 0) {
      console.log(chalk.yellow("No config files found. Run 'Copy App Dist Files' first."));
      return false;
    }

    console.log(chalk.blue(`Found ${totalFilesToProcess} config files to update.`));

    // Store the config for later use
    this.loadedConfig = config;

    return true;
  }

  async execute(progress: GenericBar): Promise<void> {
    if (!this.awsUtils) {
      throw new Error("AWS utilities not initialized. Call prompt() first.");
    }

    console.log(chalk.blue(`Starting to process configuration files...`));

    let totalUpdated = 0;
    let totalFailed = 0;
    let filesProcessed = 0;

    // Add null/undefined check before iterating loadedConfig
    if (!this.loadedConfig) {
      throw new Error("No configuration loaded. Cannot process services.");
    }
    // Loop through services → files → mappings
    for (const serviceConfig of this.loadedConfig) {
      const serviceName = serviceConfig.service;

      // Resolve placeholders for this service
      const resolvedPlaceholders = this.resolvePlaceholders(serviceConfig);
      debug(`Resolved placeholders for ${serviceName}:`, resolvedPlaceholders);

      // Use basePath from config
      if (!serviceConfig.basePath) {
        debug(`No basePath defined for service: ${serviceName}`);
        continue;
      }
      const serviceDir = path.resolve(__dirname, `../../../../../${serviceConfig.basePath}`);

      if (!serviceDir || !fs.existsSync(serviceDir)) {
        debug(`No directory found for service: ${serviceName} (${serviceDir})`);
        continue;
      }

      console.log(chalk.cyan(`\n📁 Processing service: ${serviceName.toUpperCase()}`));

      // Loop through files for this service
      for (const fileConfig of serviceConfig.files) {
        const relativePath = fileConfig.path;
        const fullPath = path.join(serviceDir, relativePath);

        // Check if file exists before processing
        if (!fs.existsSync(fullPath)) {
          debug(`Skipping non-existent file: ${fullPath}`);
          continue;
        }

        const fileName = path.basename(fileConfig.path);
        console.log(chalk.blue(`  📄 Processing file: ${fileName}`));

        try {
          // Process mappings for this file
          const result = await this.processConfigFile(fullPath, fileConfig.mappings, serviceName, resolvedPlaceholders);
          totalUpdated += result.updated;
          totalFailed += result.failed;
          filesProcessed++;
        } catch (error) {
          throw new Error(`Failed to update ${fullPath}: ${error}`);
        }
      }
    }

    // Show final summary
    const cacheStats = this.awsUtils.getCacheStats();
    console.log(chalk.blue(`\n📊 Summary:`));
    console.log(`  Files processed: ${filesProcessed}`);
    console.log(`  Values updated: ${chalk.green(totalUpdated)}`);
    console.log(`  Values failed: ${chalk.red(totalFailed)}`);
    console.log(`  AWS API calls cached: ${chalk.blue(cacheStats.parameterCount + cacheStats.secretCount)}`);

    if (totalFailed > 0) {
      console.log(
        chalk.yellow(`\n⚠️  Process completed with ${totalFailed} failures. Check the error messages above.`),
      );
    } else if (totalUpdated > 0) {
      console.log(chalk.green(`\n✅ AWS secrets and parameters synchronized successfully!`));
    } else {
      console.log(chalk.yellow(`\n⚠️  No values were updated. Check AWS permissions and resource names.`));
    }
  }

  private async processConfigFile(
    configFile: string,
    mappings: ConfigMapping[],
    serviceName: string,
    resolvedPlaceholders: Record<string, string>,
  ): Promise<{ updated: number; failed: number }> {
    if (!mappings || mappings.length === 0) {
      debug(chalk.yellow(`No mappings defined for ${configFile}`));
      return { updated: 0, failed: 0 };
    }

    if (!this.awsUtils) {
      throw new Error("AWS utilities not initialized");
    }

    console.log(chalk.blue(`    Processing ${mappings.length} mappings...`));
    debug(chalk.blue(`Processing ${configFile} with ${mappings.length} mappings...`));

    let updatedCount = 0;
    let failedCount = 0;

    // Create a LaminasConfig instance for this file
    const configUtils = new LaminasConfig(configFile);

    try {
      // Collect all update operations first
      const updateOperations: Array<{
        configPath: string[];
        finalValue: string;
        resourceType: string;
        mapping: ConfigMapping;
      }> = [];

      // Process each mapping to collect values
      for (const rawMapping of mappings) {
        // Replace placeholders in AWS path using resolved placeholders
        let resolvedAwsPath = rawMapping.awsPath;
        for (const [placeholder, value] of Object.entries(resolvedPlaceholders)) {
          resolvedAwsPath = resolvedAwsPath.replace(new RegExp(`\\{${placeholder}\\}`, "g"), value);
        }

        const mapping: ConfigMapping = {
          ...rawMapping,
          awsPath: resolvedAwsPath,
        };

        // Use awsUtils to fetch the value
        let result: { value: string | null; error?: string };
        if (mapping.type === "secret" && mapping.secretKey) {
          result = await this.awsUtils.getSecretValue(mapping.awsPath, mapping.secretKey);
        } else if (mapping.type === "secret") {
          result = await this.awsUtils.getSecret(mapping.awsPath);
        } else {
          result = await this.awsUtils.getParameter(mapping.awsPath);
        }

        const resourceType = mapping.type === "parameter" ? "parameter" : "secret";
        const configPath = mapping.configPath.join(".");

        if (result.value !== null) {
          // Apply prepend and append if specified
          let finalValue = result.value;
          if (mapping.prepend) {
            finalValue = mapping.prepend + finalValue;
            debug(`Applied prepend "${mapping.prepend}" to ${configPath}`);
          }
          if (mapping.append) {
            finalValue = finalValue + mapping.append;
            debug(`Applied append "${mapping.append}" to ${configPath}`);
          }

          updateOperations.push({
            configPath: mapping.configPath,
            finalValue,
            resourceType,
            mapping,
          });

          console.log(
            chalk.green(`      ✓ Found ${resourceType} value for ${configPath} in ${serviceName.toUpperCase()}`),
          );
          updatedCount++;
        } else {
          const errorMsg = result.error || "Unknown error";
          console.log(chalk.red(`      ✗ Failed to get ${resourceType} for ${configPath}: ${errorMsg}`));
          console.log(chalk.gray(`         AWS path: ${mapping.awsPath}`));
          debug(`Failed to fetch ${resourceType} from AWS path: ${mapping.awsPath}`);
          failedCount++;
        }
      }

      // Apply all updates using LaminasConfig
      if (updateOperations.length > 0) {
        for (const operation of updateOperations) {
          try {
            // Check if the path exists in the PHP config file
            const existingValue = await configUtils.getConfigValue(operation.configPath);
            if (existingValue === undefined) {
              console.log(
                chalk.yellow(
                  `      ⚠️  Config path ${operation.configPath.join(".")} not found in ${path.basename(configFile)}`,
                ),
              );
              console.log(chalk.gray(`         Check if the path is correct in mappings.json`));
              debug(`Config path does not exist: ${operation.configPath.join(".")}`);
              updatedCount--;
              failedCount++;
              continue;
            }

            await configUtils.setConfigValue(operation.configPath, operation.finalValue);
            debug(`Set config value at ${operation.configPath.join(".")}`);
          } catch (error: any) {
            console.log(
              chalk.yellow(
                `      ⚠️  Found ${operation.resourceType} value for ${operation.configPath.join(".")} but failed to update: ${error.message}`,
              ),
            );
            debug(`Config update failed for ${operation.configPath.join(".")}: ${error.message}`);
            updatedCount--;
            failedCount++;
          }
        }
      }

      // Save the file if there were updates
      if (updatedCount > 0) {
        await configUtils.saveConfig();
        console.log(chalk.blue(`    ✓ Updated ${updatedCount} values in ${path.basename(configFile)}`));
        debug(chalk.green(`Successfully updated ${path.basename(configFile)}`));
      } else {
        console.log(chalk.yellow(`    No values updated in ${path.basename(configFile)}`));
      }

      return { updated: updatedCount, failed: failedCount };
    } catch (error) {
      throw error;
    }
  }
}
