import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import { SecretsManagerClient, GetSecretValueCommand } from "@aws-sdk/client-secrets-manager";
import { SSMClient, GetParameterCommand } from "@aws-sdk/client-ssm";
import { STSClient, GetCallerIdentityCommand } from "@aws-sdk/client-sts";
import { fromEnv } from "@aws-sdk/credential-providers";
import { Engine } from "php-parser";
import exec from "../exec";

const debug = createDebug("refresh:actions:SyncAwsSecretsAndParameters");

const phpAppDirectoryNames = ["api", "selfserve", "internal"];
const phpAppDirectories = phpAppDirectoryNames.map((dir) => path.resolve(__dirname, `../../../../app/${dir}`));

interface ConfigMapping {
  configPath: string[];
  awsPath: string;
  type: "parameter" | "secret";
  secretKey?: string; // For secrets manager JSON, the key within the JSON object
  prepend?: string; // Optional string to add before the value
  append?: string; // Optional string to add after the value
}

export default class SyncAwsSecretsAndParameters implements ActionInterface {
  private selectedEnvironment: string = "";
  private loadedConfig: any = null;
  private awsCache: Map<string, { value: any; error?: string; parsedJson?: any }> = new Map();

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
  private resolvePlaceholders(serviceConfig: any): any {
    const context = {
      environment: this.selectedEnvironment,
      service: serviceConfig.service,
    };

    // Resolve placeholder definitions
    const resolvedPlaceholders: Record<string, string> = {};
    for (const [key, value] of Object.entries(serviceConfig.placeholders)) {
      resolvedPlaceholders[key] = this.evaluatePlaceholder(value as string, context);
    }

    return resolvedPlaceholders;
  }

  /**
   * Get AWS region from environment variable with fallback
   */
  private getAwsRegion(): string {
    return process.env.AWS_REGION || process.env.AWS_DEFAULT_REGION || "eu-west-1";
  }

  /**
   * Validate AWS credentials by checking caller identity
   */
  private async validateAwsCredentials(credentials: any): Promise<void> {
    const region = this.getAwsRegion();
    const stsClient = new STSClient({
      region,
      credentials,
    });

    try {
      const command = new GetCallerIdentityCommand({});
      const response = await stsClient.send(command);

      console.log(chalk.blue(`✓ AWS credentials valid - User: ${response.Arn?.split("/").pop()} (${region})`));
      debug(`AWS Account: ${response.Account}, User ARN: ${response.Arn}, Region: ${region}`);
    } catch (error: any) {
      if (error.name === "ExpiredTokenException") {
        throw new Error("AWS session has expired. Please refresh your credentials: Then try again.");
      }
      throw new Error(`AWS credential validation failed: ${error.message || error.toString()}`);
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

    // Validate AWS credentials early
    const credentials = fromEnv();
    try {
      await this.validateAwsCredentials(credentials);
    } catch (error: any) {
      console.error(chalk.red(`❌ ${error.message}`));
      return false;
    }

    // Load configuration and count files that exist
    const configPath = path.resolve(__dirname, "../config/config-mappings.json");
    const config = JSON.parse(fs.readFileSync(configPath, "utf-8"));

    let totalFilesToProcess = 0;

    // Loop through configured services to check file existence and count
    for (const serviceConfig of config) {
      const serviceName = serviceConfig.service;

      // Find the corresponding app directory or use basePath from config
      const serviceDir = serviceConfig.basePath
        ? path.resolve(__dirname, `../../../../${serviceConfig.basePath}`)
        : phpAppDirectories.find((dir) => path.basename(dir) === serviceName.toLowerCase());

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
    // Use environment credentials explicitly (MFA-verified ones)
    const credentials = fromEnv();
    const region = this.getAwsRegion();

    const secretsClient = new SecretsManagerClient({
      region,
      credentials,
    });
    const ssmClient = new SSMClient({
      region,
      credentials,
    });

    console.log(chalk.blue(`Starting to process configuration files...`));

    let totalUpdated = 0;
    let totalFailed = 0;
    let filesProcessed = 0;

    // Loop through services → files → mappings
    for (const serviceConfig of this.loadedConfig) {
      const serviceName = serviceConfig.service;

      // Resolve placeholders for this service
      const resolvedPlaceholders = this.resolvePlaceholders(serviceConfig);
      debug(`Resolved placeholders for ${serviceName}:`, resolvedPlaceholders);

      // Find the corresponding app directory or use basePath
      const serviceDir = serviceConfig.basePath
        ? path.resolve(__dirname, `../../../../${serviceConfig.basePath}`)
        : phpAppDirectories.find((dir) => path.basename(dir) === serviceName.toLowerCase());

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
          const result = await this.processConfigFile(
            fullPath,
            fileConfig.mappings,
            serviceName,
            resolvedPlaceholders,
            secretsClient,
            ssmClient,
          );
          totalUpdated += result.updated;
          totalFailed += result.failed;
          filesProcessed++;
        } catch (error) {
          throw new Error(`Failed to update ${fullPath}: ${error}`);
        }
      }
    }

    // Show final summary
    console.log(chalk.blue(`\n📊 Summary:`));
    console.log(`  Files processed: ${filesProcessed}`);
    console.log(`  Values updated: ${chalk.green(totalUpdated)}`);
    console.log(`  Values failed: ${chalk.red(totalFailed)}`);
    console.log(`  AWS API calls: ${chalk.blue(this.awsCache.size)} (cached to avoid duplicates)`);

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
    secretsClient: SecretsManagerClient,
    ssmClient: SSMClient,
  ): Promise<{ updated: number; failed: number }> {
    if (!mappings || mappings.length === 0) {
      debug(chalk.yellow(`No mappings defined for ${configFile}`));
      return { updated: 0, failed: 0 };
    }

    console.log(chalk.blue(`    Processing ${mappings.length} mappings...`));
    debug(chalk.blue(`Processing ${configFile} with ${mappings.length} mappings...`));

    let updatedCount = 0;
    let failedCount = 0;

    // Create backup
    const backupFile = `${configFile}.backup`;
    fs.copyFileSync(configFile, backupFile);

    try {
      // Read and parse the PHP file once
      const phpContent = fs.readFileSync(configFile, "utf-8");
      const parser = new Engine({
        parser: {
          extractDoc: true,
          php7: true,
        },
        ast: {
          withPositions: true,
        },
      });

      const ast = parser.parseCode(phpContent, configFile);
      let updatedContent = phpContent;

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

        const result = await this.fetchConfigValue(
          mapping.awsPath,
          mapping.type,
          secretsClient,
          ssmClient,
          mapping.secretKey,
        );

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
          debug(`AWS path: ${mapping.awsPath}`);
          failedCount++;
        }
      }

      // Apply all updates in one pass using the pre-parsed AST
      if (updateOperations.length > 0) {
        const bulkUpdateResult = this.updatePhpConfigValuesBulk(updatedContent, ast, updateOperations);
        if (bulkUpdateResult.success) {
          updatedContent = bulkUpdateResult.content;
        } else {
          // Fallback to individual updates if bulk update fails
          for (const operation of updateOperations) {
            const updateResult = await this.updatePhpConfigValue(
              updatedContent,
              operation.configPath,
              operation.finalValue,
            );
            if (updateResult.success) {
              updatedContent = updateResult.content;
            } else {
              console.log(
                chalk.yellow(
                  `      ⚠️  Found ${operation.resourceType} value for ${operation.configPath.join(".")} but failed to update: ${updateResult.error}`,
                ),
              );
              debug(`Config update failed for ${operation.configPath.join(".")}: ${updateResult.error}`);
              updatedCount--;
              failedCount++;
            }
          }
        }
      }

      // Only write file if there were updates
      if (updatedCount > 0) {
        fs.writeFileSync(configFile, updatedContent);

        // Validate PHP syntax
        await this.validatePhpSyntax(configFile);

        console.log(chalk.blue(`    ✓ Updated ${updatedCount} values in ${path.basename(configFile)}`));
        debug(chalk.green(`Successfully updated ${path.basename(configFile)}`));
      } else {
        console.log(chalk.yellow(`    No values updated in ${path.basename(configFile)}`));
      }

      // Remove backup after successful validation
      fs.unlinkSync(backupFile);

      return { updated: updatedCount, failed: failedCount };
    } catch (error) {
      // Restore backup on failure
      fs.copyFileSync(backupFile, configFile);
      fs.unlinkSync(backupFile);
      throw error;
    }
  }

  private async fetchConfigValue(
    awsPath: string,
    type: "parameter" | "secret",
    secretsClient: SecretsManagerClient,
    ssmClient: SSMClient,
    secretKey?: string,
  ): Promise<{ value: string | null; error?: string }> {
    // Create cache key (for secrets, don't include secretKey since we cache the whole JSON)
    const cacheKey = `${type}:${awsPath}`;

    // Check cache first
    if (!this.awsCache.has(cacheKey)) {
      debug(`Fetching ${type} from AWS: ${awsPath}`);

      try {
        if (type === "secret") {
          const command = new GetSecretValueCommand({ SecretId: awsPath });
          const response = await secretsClient.send(command);

          if (!response.SecretString) {
            this.awsCache.set(cacheKey, { value: null, error: "Secret exists but has no value" });
          } else {
            // Cache the raw secret string
            this.awsCache.set(cacheKey, { value: response.SecretString });
          }
        } else {
          const command = new GetParameterCommand({
            Name: awsPath,
            WithDecryption: true,
          });
          const response = await ssmClient.send(command);
          const value = response.Parameter?.Value;
          if (!value) {
            this.awsCache.set(cacheKey, { value: null, error: "Parameter exists but has no value" });
          } else {
            this.awsCache.set(cacheKey, { value });
          }
        }
      } catch (error: any) {
        let errorMessage = error.message || error.toString();

        // Handle specific AWS error types
        if (error.name === "AccessDeniedException") {
          errorMessage = `Access denied - insufficient permissions to read ${type}`;
        } else if (error.name === "ResourceNotFoundException" || error.name === "ParameterNotFound") {
          errorMessage = `${type} not found`;
        } else if (error.name === "ExpiredTokenException") {
          errorMessage = `AWS session expired`;
        }

        this.awsCache.set(cacheKey, { value: null, error: errorMessage });
      }
    } else {
      debug(`Using cached ${type}: ${awsPath}`);
    }

    // Get from cache
    const cached = this.awsCache.get(cacheKey)!;

    // If it's a secret with a specific key, extract from JSON
    if (type === "secret" && secretKey && cached.value) {
      // Use cached parsed JSON if available
      if (!cached.parsedJson) {
        try {
          cached.parsedJson = JSON.parse(cached.value);
          // Update cache with parsed JSON
          this.awsCache.set(cacheKey, cached);
        } catch (parseError: any) {
          return { value: null, error: `Error parsing JSON secret: ${parseError.message || parseError.toString()}` };
        }
      }

      const keyValue = cached.parsedJson[secretKey];
      if (!keyValue) {
        return { value: null, error: `Key '${secretKey}' not found in secret JSON` };
      }
      return { value: keyValue };
    }

    return cached;
  }

  /**
   * Update multiple PHP config values in one pass using pre-parsed AST
   */
  private updatePhpConfigValuesBulk(
    phpContent: string,
    ast: any,
    operations: Array<{
      configPath: string[];
      finalValue: string;
      resourceType: string;
      mapping: ConfigMapping;
    }>,
  ): { content: string; success: boolean; error?: string } {
    try {
      // Find all target locations in the AST
      const updatePositions: Array<{
        startPos: number;
        endPos: number;
        newValue: string;
        configPath: string[];
      }> = [];

      for (const operation of operations) {
        const result = this.traverseAndUpdateAST(ast, operation.configPath, operation.finalValue);
        if (result.found && result.lineInfo?.valueNode?.loc) {
          const valueStart = result.lineInfo.valueNode.loc.start;
          const valueEnd = result.lineInfo.valueNode.loc.end;
          if (valueStart && valueEnd) {
            const startPos = this.calculateCharPosition(phpContent, valueStart);
            const endPos = this.calculateCharPosition(phpContent, valueEnd);
            const escapedValue = operation.finalValue.replace(/\\/g, "\\\\").replace(/'/g, "\\'");
            updatePositions.push({
              startPos,
              endPos,
              newValue: `'${escapedValue}'`,
              configPath: operation.configPath,
            });
          }
        }
      }

      if (updatePositions.length === 0) {
        return { content: phpContent, success: false, error: "No valid update positions found" };
      }

      // Sort positions by start position in descending order to avoid position shifts
      updatePositions.sort((a, b) => b.startPos - a.startPos);

      // Apply all updates
      let updatedContent = phpContent;
      for (const position of updatePositions) {
        const beforeValue = updatedContent.substring(0, position.startPos);
        const afterValue = updatedContent.substring(position.endPos);
        updatedContent = `${beforeValue}${position.newValue}${afterValue}`;
        debug(
          `Updated ${position.configPath.join(".")} using bulk update at position ${position.startPos}-${position.endPos}`,
        );
      }

      return { content: updatedContent, success: true };
    } catch (error: any) {
      return {
        content: phpContent,
        success: false,
        error: `Bulk update failed: ${error.message || error.toString()}`,
      };
    }
  }

  private async updatePhpConfigValue(
    phpContent: string,
    configPath: string[],
    newValue: string,
  ): Promise<{ content: string; success: boolean; error?: string }> {
    const parser = new Engine({
      parser: {
        extractDoc: true,
        php7: true,
      },
      ast: {
        withPositions: true,
      },
    });

    try {
      const ast = parser.parseCode(phpContent, "config.php");

      // Find and update the value using AST traversal and modification
      const result = this.traverseAndUpdateAST(ast, configPath, newValue);

      if (!result.found) {
        return {
          content: phpContent,
          success: false,
          error: `Config key '${configPath.join(".")}' not found in PHP array structure`,
        };
      }

      if (result.found) {
        // Use AST position-based replacement
        const updatedContent = this.updateWithASTPositioning(phpContent, configPath, newValue, result.lineInfo);
        if (updatedContent) {
          return { content: updatedContent, success: true };
        }
      }

      return {
        content: phpContent,
        success: false,
        error: `Failed to update config key '${configPath.join(".")}'`,
      };
    } catch (parseError: any) {
      return {
        content: phpContent,
        success: false,
        error: `PHP parsing failed: ${parseError.message || parseError.toString()}`,
      };
    }
  }

  /**
   * Traverse AST to find and update the config value
   */
  private traverseAndUpdateAST(ast: any, configPath: string[], newValue: string): { found: boolean; lineInfo?: any } {
    try {
      debug(`Looking for config path: ${configPath.join(".")}`);

      // Find the return statement with the array
      const returnNode = this.findReturnArrayNode(ast);
      if (!returnNode) {
        debug("No return array node found in AST");
        return { found: false };
      }

      debug(`Found return array node with ${returnNode.expr?.items?.length || 0} items`);

      // Navigate through the config path to find the target location
      const result = this.findConfigPath(returnNode.expr, configPath, 0);
      debug(`Path finding result: found=${result.found}`);
      return result;
    } catch (error) {
      debug(`AST traversal error: ${error}`);
      return { found: false };
    }
  }

  /**
   * Find the return statement that contains the main config array
   */
  private findReturnArrayNode(node: any): any {
    if (!node) return null;

    if (node.kind === "return" && node.expr && node.expr.kind === "array") {
      return node;
    }

    // Recursively search in child nodes
    for (const key in node) {
      if (typeof node[key] === "object" && node[key] !== null) {
        if (Array.isArray(node[key])) {
          for (const item of node[key]) {
            const result = this.findReturnArrayNode(item);
            if (result) return result;
          }
        } else {
          const result = this.findReturnArrayNode(node[key]);
          if (result) return result;
        }
      }
    }

    return null;
  }

  /**
   * Find the config path in AST to get positioning info
   */
  private findConfigPath(arrayNode: any, configPath: string[], depth: number): { found: boolean; lineInfo?: any } {
    if (!arrayNode || !arrayNode.items) {
      debug(`No array items at depth ${depth}`);
      return { found: false };
    }

    const targetKey = configPath[depth];
    const isLastKey = depth === configPath.length - 1;

    debug(
      `Looking for key '${targetKey}' at depth ${depth} (${isLastKey ? "final" : "intermediate"}). Array has ${arrayNode.items.length} items`,
    );

    // Find the array item with matching key
    for (let i = 0; i < arrayNode.items.length; i++) {
      const item = arrayNode.items[i];
      if (item.kind === "entry") {
        const keyInfo = this.getKeyInfo(item.key);
        debug(`  Item ${i}: key="${keyInfo}" (kind: ${item.key?.kind})`);

        if (this.matchesKey(item.key, targetKey)) {
          debug(`  ✓ Key matches! ${isLastKey ? "This is the target." : "Continuing deeper..."}`);

          if (isLastKey) {
            // Found the target - return position info
            return {
              found: true,
              lineInfo: {
                key: targetKey,
                keyNode: item.key,
                valueNode: item.value,
                line: item.loc?.start?.line,
                column: item.loc?.start?.column,
              },
            };
          } else {
            // Continue traversing deeper
            if (item.value && item.value.kind === "array") {
              return this.findConfigPath(item.value, configPath, depth + 1);
            } else {
              debug(`  ✗ Expected array but got ${item.value?.kind}`);
              return { found: false };
            }
          }
        }
      }
    }

    debug(`  No matching key found for '${targetKey}' at depth ${depth}`);
    return { found: false };
  }

  /**
   * Get string representation of a key for debugging
   */
  private getKeyInfo(keyNode: any): string {
    if (!keyNode) return "null";
    switch (keyNode.kind) {
      case "string":
        return keyNode.value;
      case "number":
        return keyNode.value.toString();
      case "identifier":
        return keyNode.name;
      default:
        return `unknown(${keyNode.kind})`;
    }
  }

  /**
   * Update PHP config using precise AST positioning
   */
  private updateWithASTPositioning(
    phpContent: string,
    configPath: string[],
    newValue: string,
    lineInfo: any,
  ): string | null {
    const escapedValue = newValue.replace(/\\/g, "\\\\").replace(/'/g, "\\'");

    // Use AST position information for precise replacement
    if (lineInfo && lineInfo.valueNode && lineInfo.valueNode.loc) {
      const valueStart = lineInfo.valueNode.loc.start;
      const valueEnd = lineInfo.valueNode.loc.end;

      if (valueStart && valueEnd) {
        const startPos = this.calculateCharPosition(phpContent, valueStart);
        const endPos = this.calculateCharPosition(phpContent, valueEnd);

        // Replace just the value part with proper quotes
        const beforeValue = phpContent.substring(0, startPos);
        const afterValue = phpContent.substring(endPos);
        const updatedContent = `${beforeValue}'${escapedValue}'${afterValue}`;

        debug(`Updated ${configPath.join(".")} using precise AST position replacement (chars ${startPos}-${endPos})`);
        return updatedContent;
      }
    }

    debug(`No position info available for ${configPath.join(".")} - unable to update`);
    return null;
  }

  /**
   * Calculate character position from line/column coordinates
   */
  private calculateCharPosition(phpContent: string, position: { line: number; column: number }): number {
    const lines = phpContent.split("\n");
    let charPos = 0;

    // Add up all complete lines before target line
    for (let i = 0; i < position.line - 1; i++) {
      charPos += lines[i].length + 1; // +1 for newline character
    }

    // Add column offset within target line
    charPos += position.column;

    return charPos;
  }

  /**
   * Check if an AST key node matches the target key string
   */
  private matchesKey(keyNode: any, targetKey: string): boolean {
    if (!keyNode) return false;

    switch (keyNode.kind) {
      case "string":
        return keyNode.value === targetKey;
      case "number":
        return keyNode.value.toString() === targetKey;
      case "identifier":
        return keyNode.name === targetKey;
      case "constref":
        // Handle PHP constants like CURLOPT_USERPWD
        return keyNode.name === targetKey;
      case "name":
        // Handle PHP constants (another kind for constants)
        return keyNode.name === targetKey;
      default:
        return false;
    }
  }

  private async validatePhpSyntax(configFile: string): Promise<void> {
    try {
      exec(`php -l "${configFile}"`, debug);
      debug(chalk.green(`PHP syntax validation passed for ${path.basename(configFile)}`));
    } catch (error) {
      throw new Error(`PHP syntax validation failed for ${configFile}: ${error}`);
    }
  }
}
