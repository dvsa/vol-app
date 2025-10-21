import fs from "fs";
import path from "path";
import { Engine } from "php-parser";
import { exec } from "child_process";
import { promisify } from "util";
import createDebug from "debug";

const execAsync = promisify(exec);
const debug = createDebug("refresh:utils:laminasConfigUtils");

/**
 * Laminas Config Parser/Writer Utility
 * Provides methods to parse and write Laminas-style PHP configuration files.
 */
export interface ParsedConfig {
  isLoaded: boolean;
  hasChanges: boolean;
  filePath: string;
  backupPath?: string;
}

export class LaminasConfig {
  private readonly filePath: string;
  private originalContent: string | null = null;
  private ast: any = null;
  private hasChanges = false;
  private isLoaded = false;
  private backupPath: string | null = null;

  constructor(filePath: string) {
    this.filePath = path.resolve(filePath);
  }

  /**
   * Gets the current state information.
   */
  getState(): ParsedConfig {
    return {
      isLoaded: this.isLoaded,
      hasChanges: this.hasChanges,
      filePath: this.filePath,
      backupPath: this.backupPath || undefined,
    };
  }

  /**
   * Checks if the configuration file exists.
   */
  private fileExists(): boolean {
    return fs.existsSync(this.filePath);
  }

  /**
   * Loads the configuration into memory if not already loaded.
   */
  private async loadConfig(): Promise<void> {
    if (!this.isLoaded) {
      if (!this.fileExists()) {
        throw new Error(`Configuration file not found: ${this.filePath}`);
      }

      this.originalContent = fs.readFileSync(this.filePath, "utf-8");
      const parser = new Engine({
        parser: {
          extractDoc: true, // Extract PHPDoc comments
        },
        ast: {
          withPositions: true, // Track line/column positions for precise updates
        },
      });

      try {
        this.ast = parser.parseCode(this.originalContent, this.filePath);
        this.isLoaded = true;
        debug(`Loaded config file: ${this.filePath}`);
      } catch (error: any) {
        throw new Error(`Failed to parse PHP config file: ${error.message}`);
      }
    }
  }

  /**
   * Gets a value from the configuration using an array path.
   * @param path Array of keys representing the path.
   * @returns The value at the specified path, or undefined if not found.
   */
  async getConfigValue(path: string[]): Promise<any> {
    await this.loadConfig();

    const returnNode = this.findReturnArrayNode(this.ast);
    if (!returnNode) {
      return undefined;
    }

    const result = this.findConfigPath(returnNode.expr, path, 0);
    if (!result.found) {
      return undefined;
    }

    return this.astNodeToValue(result.lineInfo?.valueNode);
  }

  /**
   * Sets a value in the configuration using an array path.
   * @param path Array of keys representing the path.
   * @param value Value to set.
   */
  async setConfigValue(path: string[], value: any): Promise<void> {
    await this.loadConfig();

    const returnNode = this.findReturnArrayNode(this.ast);
    if (!returnNode) {
      throw new Error("Could not find return array in PHP config file");
    }

    // Navigate and create path if needed
    this.ensureConfigPath(returnNode.expr, path, value);

    // Mark as having changes after successful path update
    this.hasChanges = true;
    debug(`Set config value at path: ${path.join(".")}`);
  }

  /**
   * Writes the in-memory configuration state back to the file.
   * Creates a backup and validates syntax before finalizing.
   */
  async saveConfig(): Promise<void> {
    if (!this.isLoaded) {
      throw new Error("Configuration not loaded - nothing to save");
    }

    if (!this.hasChanges) {
      debug("No changes to save");
      return;
    }

    // Create backup before making changes
    this.backupPath = `${this.filePath}.backup`;
    fs.copyFileSync(this.filePath, this.backupPath);

    const tempPath = `${this.filePath}.tmp`;

    try {
      // Generate updated content using AST positions
      const updatedContent = this.generateUpdatedContent();

      // Write to temporary file first
      fs.writeFileSync(tempPath, updatedContent, "utf-8");

      // Validate PHP syntax
      await this.validatePhpSyntax(tempPath);

      // Move temp file to final location
      fs.renameSync(tempPath, this.filePath);

      // Update our state
      this.originalContent = updatedContent;
      this.hasChanges = false;

      // Clean up backup
      fs.unlinkSync(this.backupPath);
      this.backupPath = null;

      debug(`Successfully saved config file: ${this.filePath}`);
    } catch (error) {
      // Clean up temp file if it exists
      if (fs.existsSync(tempPath)) {
        fs.unlinkSync(tempPath);
      }

      // Restore from backup on any failure
      if (this.backupPath && fs.existsSync(this.backupPath)) {
        fs.copyFileSync(this.backupPath, this.filePath);
        fs.unlinkSync(this.backupPath);
        this.backupPath = null;
      }
      throw error;
    }
  }

  /**
   * Validates the PHP syntax of the given file.
   */
  private async validatePhpSyntax(filePath: string): Promise<void> {
    try {
      const { stderr } = await execAsync(`php -l "${filePath}"`);
      if (stderr && !stderr.includes("No syntax errors detected")) {
        throw new Error(`PHP syntax error: ${stderr}`);
      }
      debug(`PHP syntax validation passed for ${path.basename(filePath)}`);
    } catch (error: any) {
      throw new Error(`PHP syntax validation failed: ${error.message}`);
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
        return keyNode.name === targetKey;
      case "name":
        return keyNode.name === targetKey;
      default:
        return false;
    }
  }

  /**
   * Convert an AST value node to JavaScript value
   */
  private astNodeToValue(node: any): any {
    if (!node) return undefined;

    switch (node.kind) {
      case "string":
        return node.value;
      case "number":
        return node.value;
      case "boolean":
        return node.value;
      case "array":
        const result: any = {};
        if (node.items) {
          for (const item of node.items) {
            if (item.kind === "entry" && item.key) {
              const key = this.astNodeToValue(item.key);
              const value = this.astNodeToValue(item.value);
              result[key] = value;
            }
          }
        }
        return result;
      case "null":
        return null;
      default:
        debug(`Unknown AST node kind: ${node.kind}`);
        return undefined;
    }
  }

  /**
   * Ensure config path exists, creating nodes as needed
   */
  private ensureConfigPath(arrayNode: any, configPath: string[], finalValue: any): void {
    let currentArray = arrayNode;

    for (let depth = 0; depth < configPath.length; depth++) {
      const targetKey = configPath[depth];
      const isLastKey = depth === configPath.length - 1;

      // Find existing entry
      let foundEntry = null;
      if (currentArray.items) {
        for (const item of currentArray.items) {
          if (item.kind === "entry" && this.matchesKey(item.key, targetKey)) {
            foundEntry = item;
            break;
          }
        }
      }

      if (foundEntry) {
        if (isLastKey) {
          // Update the existing value, preserving its type and quote style
          foundEntry.value = this.updateValueNode(foundEntry.value, finalValue);
        } else {
          // Ensure it's an array for further nesting
          if (foundEntry.value.kind !== "array") {
            foundEntry.value = {
              kind: "array",
              items: [],
              loc: foundEntry.value.loc,
            };
          }
          currentArray = foundEntry.value;
        }
      } else {
        // Create new entry
        if (!currentArray.items) {
          currentArray.items = [];
        }

        const newEntry = {
          kind: "entry",
          key: {
            kind: "string",
            value: targetKey,
            isDoubleQuote: true,
          },
          value: isLastKey ? this.valueToAstNode(finalValue) : { kind: "array", items: [] },
        };

        currentArray.items.push(newEntry);

        if (!isLastKey) {
          currentArray = newEntry.value;
        }
      }
    }
  }

  /**
   * Update an existing value node, preserving its original type and quote style
   */
  private updateValueNode(existingNode: any, newValue: any): any {
    if (!existingNode) {
      return this.valueToAstNode(newValue);
    }

    // Preserve the original node structure and just update the value
    switch (existingNode.kind) {
      case "string":
        // Preserve the quote style (single vs double)
        return {
          kind: "string",
          value: String(newValue),
          isDoubleQuote: existingNode.isDoubleQuote,
          loc: existingNode.loc,
        };

      case "number":
        // Keep as number if the new value is numeric
        const numValue = Number(newValue);
        if (!isNaN(numValue) && numValue.toString() === String(newValue).trim()) {
          return {
            kind: "number",
            value: numValue,
            loc: existingNode.loc,
          };
        }
        // If new value isn't numeric, convert to string with single quotes
        return {
          kind: "string",
          value: String(newValue),
          isDoubleQuote: false,
          loc: existingNode.loc,
        };

      case "boolean":
        // Try to keep as boolean if possible
        if (typeof newValue === "boolean") {
          return {
            kind: "boolean",
            value: newValue,
            loc: existingNode.loc,
          };
        }
        // Convert to string if not boolean
        return {
          kind: "string",
          value: String(newValue),
          isDoubleQuote: false,
          loc: existingNode.loc,
        };

      case "null":
        if (newValue === null) {
          return existingNode; // Keep as-is
        }
        // Converting from null to something else - create appropriate node
        return this.valueToAstNode(newValue);

      case "array":
        // If updating an array, we need to decide: replace or merge?
        // For now, replace with new value converted to AST
        return this.valueToAstNode(newValue);

      default:
        // Unknown type - create new node
        debug(`Unknown existing node kind: ${existingNode.kind}, creating new node`);
        return this.valueToAstNode(newValue);
    }
  }

  /**
   * Convert JavaScript value to AST node (for new values)
   */
  private valueToAstNode(value: any): any {
    if (value === null) {
      return { kind: "null" };
    }

    if (typeof value === "string") {
      return {
        kind: "string",
        value: value,
        isDoubleQuote: true,
      };
    }

    if (typeof value === "number") {
      return {
        kind: "number",
        value: value,
      };
    }

    if (typeof value === "boolean") {
      return {
        kind: "boolean",
        value: value,
      };
    }

    if (Array.isArray(value)) {
      const items = value.map((item, index) => ({
        kind: "entry",
        key: { kind: "number", value: index },
        value: this.valueToAstNode(item),
      }));

      return {
        kind: "array",
        items: items,
      };
    }

    if (typeof value === "object") {
      const items = Object.entries(value).map(([key, val]) => ({
        kind: "entry",
        key: {
          kind: "string",
          value: key,
          isDoubleQuote: true,
        },
        value: this.valueToAstNode(val),
      }));

      return {
        kind: "array",
        items: items,
      };
    }

    // Fallback - treat as string
    return {
      kind: "string",
      value: String(value),
      isDoubleQuote: true,
    };
  }

  /**
   * Generate updated content from the modified AST using position-based replacement
   */
  private generateUpdatedContent(): string {
    if (!this.originalContent || !this.ast) {
      throw new Error("No original content or AST available");
    }

    // Collect all value nodes that have been modified
    const replacements: Array<{
      startPos: number;
      endPos: number;
      newValue: string;
    }> = [];

    // Find the return array and traverse it to collect replacements
    const returnNode = this.findReturnArrayNode(this.ast);
    if (returnNode && returnNode.expr) {
      this.collectReplacements(returnNode.expr, [], replacements);
    }

    if (replacements.length === 0) {
      // No changes detected
      return this.originalContent;
    }

    // Sort replacements by start position in descending order
    // This ensures we don't mess up positions when applying multiple changes
    replacements.sort((a, b) => b.startPos - a.startPos);

    // Apply all replacements
    let updatedContent = this.originalContent;
    for (const replacement of replacements) {
      const before = updatedContent.substring(0, replacement.startPos);
      const after = updatedContent.substring(replacement.endPos);
      updatedContent = before + replacement.newValue + after;
      debug(`Applied replacement at position ${replacement.startPos}-${replacement.endPos}`);
    }

    return updatedContent;
  }

  /**
   * Recursively collect value replacements from the AST
   * Only replaces scalar values (string, number, boolean, null) that we actually modified
   */
  private collectReplacements(
    arrayNode: any,
    path: string[],
    replacements: Array<{ startPos: number; endPos: number; newValue: string }>,
  ): void {
    debug("Collecting replacements for path: " + path.join("."));
    if (!arrayNode || !arrayNode.items) {
      return;
    }

    for (const item of arrayNode.items) {
      if (item.kind === "entry" && item.key && item.value) {
        const keyStr = this.getKeyInfo(item.key);
        const currentPath = [...path, keyStr];

        // Only process scalar values that we can safely replace
        const isScalar = ["string", "number", "boolean", "null"].includes(item.value.kind);

        if (isScalar && item.value.loc && item.value.loc.start && item.value.loc.end) {
          const startPos = this.calculateCharPosition(this.originalContent!, item.value.loc.start);
          const endPos = this.calculateCharPosition(this.originalContent!, item.value.loc.end);
          const originalText = this.originalContent!.substring(startPos, endPos);
          const newText = this.valueNodeToString(item.value);

          // Only add replacement if the value actually changed
          if (originalText !== newText) {
            replacements.push({ startPos, endPos, newValue: newText });
            debug(`Replacement needed for ${currentPath.join(".")}: "${originalText}" -> "${newText}"`);
          }
        }

        // Recurse into nested arrays to find scalar values within
        if (item.value.kind === "array") {
          this.collectReplacements(item.value, currentPath, replacements);
        }
      }
    }
  }

  /**
   * Convert an AST value node to its string representation
   */
  private valueNodeToString(node: any): string {
    switch (node.kind) {
      case "string":
        const quote = node.isDoubleQuote ? '"' : "'";
        const escaped = this.escapeString(node.value, node.isDoubleQuote);
        return `${quote}${escaped}${quote}`;

      case "number":
        return String(node.value);

      case "boolean":
        return node.value ? "true" : "false";

      case "null":
        return "null";

      default:
        debug(`Cannot convert node kind ${node.kind} to string`);
        return "";
    }
  }

  /**
   * Escape a string value for PHP based on quote type
   */
  private escapeString(value: string, isDoubleQuote: boolean): string {
    if (isDoubleQuote) {
      // Double-quoted strings: escape backslashes, double quotes, and special chars
      return value
        .replace(/\\/g, "\\\\")
        .replace(/"/g, '\\"')
        .replace(/\$/g, "\\$")
        .replace(/\n/g, "\\n")
        .replace(/\r/g, "\\r")
        .replace(/\t/g, "\\t");
    } else {
      // Single-quoted strings: only escape backslashes and single quotes
      return value.replace(/\\/g, "\\\\").replace(/'/g, "\\'");
    }
  }

  /**
   * Calculate character position from line/column coordinates
   */
  private calculateCharPosition(content: string, position: { line: number; column: number }): number {
    const lines = content.split("\n");
    let charPos = 0;

    // Add up all complete lines before target line
    for (let i = 0; i < position.line - 1; i++) {
      charPos += lines[i].length + 1; // +1 for newline
    }

    // Add column offset
    charPos += position.column;

    return charPos;
  }
}
