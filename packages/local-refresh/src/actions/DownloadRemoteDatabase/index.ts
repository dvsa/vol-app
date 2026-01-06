import prompts from "prompts";
import path from "node:path";
import fs from "node:fs";
import { spawn, spawnSync, ChildProcess } from "node:child_process";
import chalk from "chalk";
import ActionInterface from "../ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import exec from "../../exec";
import { Aws } from "../../utils/Aws";
import { REMOTE_DATABASES, LOCAL_DATABASE, AWS_SECRET_PASSWORD_KEY } from "./config";
import type { DownloadOptions, RemoteDatabaseConfig } from "./types";

const debug = createDebug("refresh:actions:DownloadRemoteDatabase");

/**
 * Filter out deprecation warnings and empty lines from stderr output.
 */
function filterStderr(stderr: string): string[] {
  return stderr.split("\n").filter((line) => line.trim() && !line.includes("Deprecated"));
}

// Track any spawned processes for cleanup on exit
let activeProcess: ChildProcess | null = null;

function cleanupProcess(): void {
  if (activeProcess && !activeProcess.killed) {
    debug("Cleaning up spawned process PID: %d", activeProcess.pid);
    activeProcess.kill("SIGTERM");
    // Give it a moment, then force kill if needed
    setTimeout(() => {
      if (activeProcess && !activeProcess.killed) {
        activeProcess.kill("SIGKILL");
      }
    }, 1000);
  }
}

export default class DownloadRemoteDatabase implements ActionInterface {
  private options: DownloadOptions = {
    environment: "dev",
    tableFilters: {
      excludeQueueTables: true,
      excludeHistTables: true,
      excludeReadAuditTables: true,
    },
    backupFirst: false,
  };

  private remotePassword: string = "";
  private aws: Aws | null = null;

  async prompt(): Promise<boolean> {
    // Check for existing dump files first
    const existingDumps = this.findExistingDumpFiles();

    // Step 1: Ask if user wants to download/restore remote database
    let useExistingDump = false;

    if (existingDumps.length > 0) {
      // Offer to use existing dump file
      const choices = [
        { title: "Download fresh from remote", value: "download" },
        ...existingDumps.map((f) => ({
          title: `Use existing: ${path.basename(f.path)} (${f.sizeMB} MB, ${f.age})`,
          value: f.path,
        })),
        { title: "Skip this step", value: "skip" },
      ];

      const { action } = await prompts({
        type: "select",
        name: "action",
        message: "Database dump files found. What would you like to do?",
        choices,
        initial: 0,
      });

      if (!action || action === "skip") {
        return false;
      }

      if (action !== "download") {
        useExistingDump = true;
        this.options.existingDumpFile = action;
      }
    } else {
      // No existing dumps - ask if they want to download
      const { shouldDownload } = await prompts({
        type: "confirm",
        name: "shouldDownload",
        message: "Download and restore a remote database (DEV/INT)?",
        initial: false,
      });

      if (!shouldDownload) {
        return false;
      }
    }

    // Pre-flight checks for local database (always needed)
    console.log(chalk.blue("\nRunning pre-flight checks..."));

    // Check Docker MySQL is available
    console.log(chalk.gray("  Checking Docker MySQL..."));
    if (!this.checkMysqlTools()) {
      console.error(chalk.yellow("  Make sure the db container is running: docker compose up -d db"));
      return false;
    }
    console.log(chalk.green("  Docker MySQL available"));

    // Check local database connectivity
    console.log(chalk.gray("  Testing local database connection..."));
    if (!this.testLocalConnection()) {
      console.error(chalk.red("  Cannot connect to local database."));
      console.error(chalk.yellow("  Ensure the db container is running: docker compose up -d db"));
      return false;
    }
    console.log(chalk.green("  Local database connected"));

    // Check available disk space (need ~50GB for dump file + imported data)
    const requiredSpaceGB = 50;
    const availableSpaceGB = this.getAvailableDiskSpaceGB();
    if (availableSpaceGB !== null) {
      if (availableSpaceGB < requiredSpaceGB) {
        console.error(
          chalk.red(
            `  Insufficient disk space: ${availableSpaceGB.toFixed(1)} GB available, ~${requiredSpaceGB} GB needed`,
          ),
        );
        console.error(chalk.yellow("  The dump file is ~20GB and MySQL data will be ~30GB after import."));
        return false;
      }
      console.log(chalk.green(`  Disk space OK (${availableSpaceGB.toFixed(1)} GB available)`));
    }

    // Download-specific checks (skip if using existing dump)
    if (!useExistingDump) {
      // Environment selection
      const { environment } = await prompts({
        type: "select",
        name: "environment",
        message: "Select the source environment:",
        choices: [
          { title: "DEV", value: "dev" },
          { title: "INT (QA)", value: "int" },
        ],
        initial: 0,
      });

      if (!environment) {
        return false;
      }
      this.options.environment = environment;

      // Check host MySQL tools for remote connections (VPN compatibility)
      const hostMysqlToolsAvailable = this.checkHostMysqlTools();
      if (!hostMysqlToolsAvailable) {
        console.error(chalk.red("\n  Cannot proceed: mysql/mysqldump are required on the host for VPN connectivity."));
        console.error(chalk.yellow("  Install mysql-client and ensure mysql and mysqldump are in your PATH."));
        return false;
      }

      // Check AWS credentials
      console.log(chalk.gray("  Validating AWS credentials..."));
      this.aws = new Aws();
      try {
        const identity = await this.aws.validateAwsCredentials();
        console.log(chalk.green(`  AWS credentials valid (${identity.account})`));
      } catch (error: unknown) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(chalk.red(`  AWS credentials invalid: ${message}`));
        console.error(chalk.yellow("  Authenticate with VOL nonprod and retry."));
        return false;
      }

      // Fetch remote database password from AWS Secrets Manager
      const remoteConfig = REMOTE_DATABASES[environment];
      console.log(chalk.gray(`  Fetching ${remoteConfig.displayName} database password...`));
      try {
        const secret = await this.aws.getSecretValue(remoteConfig.awsSecretPath, AWS_SECRET_PASSWORD_KEY);
        if (secret.error || !secret.value) {
          throw new Error(secret.error || "Password not found in secret");
        }
        this.remotePassword = secret.value;
        console.log(chalk.green(`  Password retrieved from AWS Secrets Manager`));
      } catch (error: unknown) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(chalk.red(`  Failed to fetch password: ${message}`));
        return false;
      }

      // Check remote database connectivity (uses host mysql for VPN)
      console.log(chalk.gray(`  Testing connection to ${remoteConfig.displayName}...`));
      if (!this.testRemoteConnection(remoteConfig)) {
        console.error(chalk.red(`  Cannot connect to ${remoteConfig.displayName} database.`));
        console.error(chalk.yellow("  Ensure you are connected to the VPN."));
        return false;
      }
      console.log(chalk.green(`  ${remoteConfig.displayName} database connected`));

      // Table filtering options
      console.log("");
      const { excludeQueueTables } = await prompts({
        type: "confirm",
        name: "excludeQueueTables",
        message: "Exclude queue and queue_hist tables? (recommended - these are large)",
        initial: true,
      });

      if (excludeQueueTables === undefined) {
        return false;
      }
      this.options.tableFilters.excludeQueueTables = excludeQueueTables;

      // Only ask about queue date filter if not excluding queue tables
      if (!excludeQueueTables) {
        const { filterQueueByDate } = await prompts({
          type: "confirm",
          name: "filterQueueByDate",
          message: "Filter queue entries by date? (delete old entries after restore)",
          initial: false,
        });

        if (filterQueueByDate) {
          const { queueDateFilter } = await prompts({
            type: "date",
            name: "queueDateFilter",
            message: "Keep queue entries from (date):",
            initial: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), // 7 days ago
          });
          this.options.tableFilters.queueDateFilter = queueDateFilter;
        }
      }

      const { excludeHistTables } = await prompts({
        type: "confirm",
        name: "excludeHistTables",
        message: "Exclude all *_hist tables? (~255 tables, significantly reduces download size)",
        initial: true,
      });

      if (excludeHistTables === undefined) {
        return false;
      }
      this.options.tableFilters.excludeHistTables = excludeHistTables;

      const { excludeReadAuditTables } = await prompts({
        type: "confirm",
        name: "excludeReadAuditTables",
        message: "Exclude all *_read_audit tables?",
        initial: true,
      });

      if (excludeReadAuditTables === undefined) {
        return false;
      }
      this.options.tableFilters.excludeReadAuditTables = excludeReadAuditTables;
    }

    // Backup option (always available)
    const { backupFirst } = await prompts({
      type: "confirm",
      name: "backupFirst",
      message: "Create a backup of the local database before restoring?",
      initial: false,
    });

    if (backupFirst === undefined) {
      return false;
    }
    this.options.backupFirst = backupFirst;

    // Summary
    console.log(chalk.blue("\nConfiguration Summary:"));
    if (useExistingDump) {
      const dumpFile = this.options.existingDumpFile!;
      const stats = fs.statSync(dumpFile);
      const sizeMB = (stats.size / 1024 / 1024).toFixed(1);
      console.log(`  Source: ${path.basename(dumpFile)} (${sizeMB} MB)`);
    } else {
      const remoteConfig = REMOTE_DATABASES[this.options.environment];
      console.log(`  Source: ${remoteConfig.displayName} (${remoteConfig.host})`);
      console.log(`  Exclude queue tables: ${this.options.tableFilters.excludeQueueTables ? "Yes" : "No"}`);
      console.log(`  Exclude *_hist tables: ${this.options.tableFilters.excludeHistTables ? "Yes" : "No"}`);
      console.log(`  Exclude *_read_audit tables: ${this.options.tableFilters.excludeReadAuditTables ? "Yes" : "No"}`);
    }
    console.log(`  Target: ${LOCAL_DATABASE.database} (local)`);
    console.log(`  Backup first: ${backupFirst ? "Yes" : "No"}`);

    const { confirmProceed } = await prompts({
      type: "confirm",
      name: "confirmProceed",
      message: chalk.yellow("This will REPLACE your local database. Proceed?"),
      initial: false,
    });

    return confirmProceed === true;
  }

  async execute(progress: GenericBar): Promise<void> {
    const usingExistingDump = !!this.options.existingDumpFile;

    // Calculate steps: backup (optional) + prepare + import + verify
    const totalSteps = this.options.backupFirst ? 4 : 3;
    progress.start(totalSteps, 0);

    // Step 1: Optional backup
    if (this.options.backupFirst) {
      console.log(chalk.blue("\nBacking up local database..."));
      const backupPath = path.join(process.cwd(), `olcs_be_backup_${Date.now()}.sql.gz`);
      this.backupLocalDatabase(backupPath);
      console.log(chalk.green(`  Backup saved to: ${backupPath}`));
      progress.increment();
    }

    // Step 2: Prepare local database
    console.log(chalk.blue("\nPreparing local database..."));
    this.prepareLocalDatabase();
    console.log(chalk.green("  Local database recreated"));
    progress.increment();

    // Step 3: Import data
    if (usingExistingDump) {
      // Import from existing dump file
      console.log(chalk.blue("\nImporting from existing dump file..."));
      this.importDumpFile(this.options.existingDumpFile!);
    } else {
      // Download and import from remote
      const remoteConfig = REMOTE_DATABASES[this.options.environment];

      console.log(chalk.blue("\nFetching table list from remote database..."));
      const allTables = this.getRemoteTables(remoteConfig);
      const excludedTables = this.getExcludedTables(allTables);
      const includedCount = allTables.length - excludedTables.length;
      console.log(chalk.green(`  ${includedCount} tables to sync (${excludedTables.length} excluded)`));

      console.log(chalk.blue("\nDownloading and restoring database..."));
      console.log(chalk.yellow("  This may take several minutes for large databases."));
      await this.streamDumpToLocal(remoteConfig, excludedTables);
    }

    // Verify the restore worked
    const tableCount = this.verifyLocalDatabase();
    if (tableCount === 0) {
      throw new Error("Database restore failed - no tables found in local database after restore");
    }
    console.log(chalk.green(`  Database restored (${tableCount} tables)`));
    progress.increment();

    // Handle queue date filtering if applicable (only for fresh downloads)
    if (
      !usingExistingDump &&
      !this.options.tableFilters.excludeQueueTables &&
      this.options.tableFilters.queueDateFilter
    ) {
      console.log(chalk.blue("\nFiltering queue entries by date..."));
      this.filterQueueByDate();
      console.log(chalk.green("  Old queue entries removed"));
    }

    progress.stop();
    console.log(chalk.green("\nDatabase restore completed successfully!"));
  }

  /**
   * Import a dump file to the local database using Docker.
   */
  private importDumpFile(dumpFile: string): void {
    const stats = fs.statSync(dumpFile);
    const sizeMB = (stats.size / 1024 / 1024).toFixed(1);
    console.log(chalk.gray(`  Importing ${path.basename(dumpFile)} (${sizeMB} MB)...`));
    console.log(chalk.gray("  (This may take a while for large files)"));

    exec(
      `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' ${LOCAL_DATABASE.database} < "${dumpFile}"`,
      debug,
    );
  }

  /**
   * Find existing database dump files in the current directory.
   */
  private findExistingDumpFiles(): Array<{ path: string; sizeMB: string; age: string }> {
    const cwd = process.cwd();
    const files: Array<{ path: string; sizeMB: string; age: string; mtime: number }> = [];

    try {
      const entries = fs.readdirSync(cwd);
      for (const entry of entries) {
        if (entry.startsWith("db_dump_") && entry.endsWith(".sql")) {
          const fullPath = path.join(cwd, entry);
          const stats = fs.statSync(fullPath);
          const sizeMB = (stats.size / 1024 / 1024).toFixed(1);
          const ageMs = Date.now() - stats.mtimeMs;
          const ageHours = Math.floor(ageMs / (1000 * 60 * 60));
          const ageMins = Math.floor((ageMs % (1000 * 60 * 60)) / (1000 * 60));
          const age = ageHours > 0 ? `${ageHours}h ${ageMins}m ago` : `${ageMins}m ago`;

          files.push({ path: fullPath, sizeMB, age, mtime: stats.mtimeMs });
        }
      }
    } catch {
      // Directory read failed, return empty
    }

    // Sort by most recent first
    return files.sort((a, b) => b.mtime - a.mtime).map(({ path, sizeMB, age }) => ({ path, sizeMB, age }));
  }

  /**
   * Get available disk space in GB for the current working directory.
   * Returns null if unable to determine.
   */
  private getAvailableDiskSpaceGB(): number | null {
    try {
      const result = exec(`df -BG "${process.cwd()}" | tail -1 | awk '{print $4}'`, debug);
      const availableStr = result.stdout.trim().replace("G", "");
      const availableGB = parseInt(availableStr, 10);
      return isNaN(availableGB) ? null : availableGB;
    } catch {
      return null;
    }
  }

  /**
   * Check if host mysql tools (mysql and mysqldump) are available.
   * Required for VPN connectivity to remote databases.
   */
  private checkHostMysqlTools(): boolean {
    try {
      exec("mysql --version 2>/dev/null || mariadb --version", debug);
      exec("mysqldump --version 2>/dev/null || mariadb-dump --version", debug);
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Get the dump command (mariadb-dump preferred over mysqldump to avoid deprecation warnings).
   */
  private getDumpCommand(): string {
    try {
      exec("which mariadb-dump", debug);
      return "mariadb-dump";
    } catch {
      return "mysqldump";
    }
  }

  /**
   * Get the mysql client command.
   */
  private getMysqlCommand(): string {
    try {
      exec("which mariadb", debug);
      return "mariadb";
    } catch {
      return "mysql";
    }
  }

  /**
   * Check if Docker MySQL is available.
   */
  private checkMysqlTools(): boolean {
    try {
      exec("docker compose exec -T db mysql --version", debug);
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Test connection to local MySQL database via Docker.
   */
  private testLocalConnection(): boolean {
    try {
      exec(
        `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' -e "SELECT 1"`,
        debug,
      );
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Build mysql command with credentials via process substitution (no file on disk).
   * Returns shell command string to execute via bash.
   */
  private buildMysqlCommand(config: RemoteDatabaseConfig, args: string[]): string {
    const mysqlCmd = this.getMysqlCommand();
    // Use process substitution to pass password securely - no file written to disk
    const configContent = `[client]\\npassword=${this.remotePassword}`;
    return `${mysqlCmd} --defaults-extra-file=<(printf '${configContent}') -h ${config.host} -P ${config.port} -u ${config.user} ${args.join(" ")} ${config.database}`;
  }

  /**
   * Build mysqldump command with credentials via process substitution.
   * Returns shell command string to execute via bash.
   */
  private buildMysqldumpCommand(config: RemoteDatabaseConfig, args: string[]): string {
    const dumpCmd = this.getDumpCommand();
    // Use process substitution to pass password securely - no file written to disk
    const configContent = `[client]\\npassword=${this.remotePassword}`;
    return `${dumpCmd} --defaults-extra-file=<(printf '${configContent}') -h ${config.host} -P ${config.port} -u ${config.user} ${args.join(" ")} ${config.database}`;
  }

  /**
   * Test connection to remote MySQL database.
   * Always uses host mysql for VPN connectivity.
   */
  private testRemoteConnection(config: RemoteDatabaseConfig): boolean {
    debug("Testing connection to %s:%d as %s", config.host, config.port, config.user);
    const cmd = this.buildMysqlCommand(config, ["-e", '"SELECT 1"']);
    const result = spawnSync("bash", ["-c", cmd], { encoding: "utf-8" });
    if (result.status !== 0) {
      debug("Connection test failed (exit %d)", result.status);
      const errorLines = filterStderr(result.stderr || "");
      if (errorLines.length > 0) {
        console.log(chalk.gray(`  Connection error: ${errorLines[0].trim()}`));
      }
    }
    return result.status === 0;
  }

  /**
   * Get list of all tables from remote database.
   * Always uses host mysql for VPN connectivity.
   */
  private getRemoteTables(config: RemoteDatabaseConfig): string[] {
    const cmd = this.buildMysqlCommand(config, ["-N", "-e", '"SHOW TABLES"']);
    const result = spawnSync("bash", ["-c", cmd], { encoding: "utf-8" });
    if (result.status !== 0) {
      const errorLines = filterStderr(result.stderr || "");
      throw new Error(`Failed to get remote tables: ${errorLines.join(" ")}`);
    }
    return (result.stdout || "")
      .split("\n")
      .map((t) => t.trim())
      .filter(Boolean);
  }

  /**
   * Get list of tables to exclude based on filter options.
   */
  private getExcludedTables(allTables: string[]): string[] {
    const excluded: string[] = [];

    if (this.options.tableFilters.excludeQueueTables) {
      excluded.push("queue", "queue_hist");
    }

    if (this.options.tableFilters.excludeHistTables) {
      excluded.push(...allTables.filter((t) => t.endsWith("_hist")));
    }

    if (this.options.tableFilters.excludeReadAuditTables) {
      excluded.push(...allTables.filter((t) => t.endsWith("_read_audit")));
    }

    return [...new Set(excluded)];
  }

  /**
   * Create a backup of the local database using Docker.
   */
  private backupLocalDatabase(backupPath: string): void {
    exec(
      `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysqldump -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' ${LOCAL_DATABASE.database} | gzip > "${backupPath}"`,
      debug,
    );
  }

  /**
   * Drop and recreate the local database, and create users needed for DEFINER clauses.
   */
  private prepareLocalDatabase(): void {
    // SQL to prepare database and create users needed for DEFINER clauses and app connectivity
    const sql = `
      DROP DATABASE IF EXISTS ${LOCAL_DATABASE.database};
      CREATE DATABASE ${LOCAL_DATABASE.database};
      CREATE USER IF NOT EXISTS 'master'@'%' IDENTIFIED BY 'master';
      GRANT ALL PRIVILEGES ON *.* TO 'master'@'%';
      CREATE USER IF NOT EXISTS 'olcsapi'@'%' IDENTIFIED BY 'olcsapi';
      GRANT ALL PRIVILEGES ON *.* TO 'olcsapi'@'%';
      CREATE USER IF NOT EXISTS 'mysql'@'%' IDENTIFIED BY 'olcs';
      GRANT ALL PRIVILEGES ON *.* TO 'mysql'@'%';
    `.replace(/\n/g, " ");

    exec(
      `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' -e "${sql}"`,
      debug,
    );
  }

  /**
   * Get mysqldump options that are compatible with the installed version.
   */
  private getMysqldumpOptions(): string[] {
    const dumpCmd = this.getDumpCommand();

    // Base options that work on all versions (MySQL 5.x+, MariaDB)
    const options = [
      "--single-transaction", // Consistent snapshot for InnoDB (widely supported)
      "--quick", // Don't buffer results in memory (widely supported)
      "--skip-lock-tables", // Don't lock tables (useful for read replicas)
      "--compress", // Compress data sent between client and server (faster over VPN)
    ];

    // Check if dump command supports --no-tablespaces (MySQL 5.7.31+, not in older MariaDB)
    try {
      const result = exec(`${dumpCmd} --help 2>&1 | grep -q 'no-tablespaces' && echo 'yes' || echo 'no'`, debug);
      if (result.stdout.trim() === "yes") {
        options.push("--no-tablespaces");
      }
    } catch {
      // If check fails, skip the option
    }

    // Check if dump command supports --set-gtid-purged (MySQL 5.6+, not MariaDB)
    try {
      const result = exec(`${dumpCmd} --help 2>&1 | grep -q 'set-gtid-purged' && echo 'yes' || echo 'no'`, debug);
      if (result.stdout.trim() === "yes") {
        options.push("--set-gtid-purged=OFF");
      }
    } catch {
      // If check fails, skip the option
    }

    // Check if dump command supports --column-statistics (MySQL 8.0+)
    // This option needs to be disabled when dumping from older servers
    try {
      const result = exec(`${dumpCmd} --help 2>&1 | grep -q 'column-statistics' && echo 'yes' || echo 'no'`, debug);
      if (result.stdout.trim() === "yes") {
        options.push("--column-statistics=0");
      }
    } catch {
      // If check fails, skip the option
    }

    return options;
  }

  /**
   * Dump remote database to a temporary file, then import to local.
   * Uses temp file approach for reliability (avoids pipe escaping issues).
   * Always uses host mysqldump for VPN connectivity.
   * Uses spawn to track PID and ensure cleanup on exit.
   */
  private async streamDumpToLocal(remoteConfig: RemoteDatabaseConfig, excludedTables: string[]): Promise<void> {
    const excludeArgs = excludedTables.flatMap((t) => ["--ignore-table", `${remoteConfig.database}.${t}`]);
    const tempFile = path.join(process.cwd(), `db_dump_${Date.now()}.sql`);
    const mysqldumpOptions = this.getMysqldumpOptions();

    // Signal handler for cleanup during dump (defined here for finally block access)
    const signalHandler = () => {
      cleanupProcess();
      process.exit(130);
    };

    try {
      // Step 1: Dump remote database to temp file (uses host mysqldump for VPN)
      console.log(chalk.gray("  Dumping remote database..."));

      const allArgs = [...excludeArgs, ...mysqldumpOptions];
      const bashCmd = this.buildMysqldumpCommand(remoteConfig, allArgs);

      debug("Running mysqldump with %d exclude args and %d options", excludeArgs.length, mysqldumpOptions.length);

      // Create output file stream
      const outputStream = fs.createWriteStream(tempFile);
      let stderrOutput = "";

      // Spawn bash with process substitution for secure password handling
      const dumpProcess = spawn("bash", ["-c", bashCmd], {
        stdio: ["ignore", "pipe", "pipe"],
      });

      // Track the process for cleanup
      activeProcess = dumpProcess;
      debug("Started dump process PID: %d", dumpProcess.pid);

      // Register signal handlers for cleanup during dump
      process.on("SIGINT", signalHandler);
      process.on("SIGTERM", signalHandler);

      // Pipe stdout to file
      dumpProcess.stdout.pipe(outputStream);

      // Collect stderr
      dumpProcess.stderr.on("data", (data: Buffer) => {
        stderrOutput += data.toString();
      });

      // Start file size reporter
      const sizeReporter = setInterval(() => {
        try {
          if (fs.existsSync(tempFile)) {
            const size = fs.statSync(tempFile).size;
            const sizeMB = (size / 1024 / 1024).toFixed(1);
            process.stdout.write(`\r  Dumping... ${sizeMB} MB`);
          }
        } catch {
          // File might not exist yet or be in use
        }
      }, 2000);

      // Wait for process to complete
      const exitCode = await this.waitForProcess(dumpProcess);
      clearInterval(sizeReporter);
      activeProcess = null;
      process.stdout.write("\r" + " ".repeat(50) + "\r"); // Clear the line

      // Close the output stream and wait for it to finish
      await new Promise<void>((resolve) => outputStream.end(resolve));

      if (exitCode !== 0) {
        console.error(chalk.red("\n  Dump failed!"));
        const errors = filterStderr(stderrOutput);
        if (errors.length > 0) {
          console.error(chalk.red(`  Stderr: ${errors.join("\n")}`));
        }
        if (fs.existsSync(tempFile)) {
          const size = fs.statSync(tempFile).size;
          console.error(chalk.yellow(`  Partial dump file: ${(size / 1024 / 1024).toFixed(2)} MB`));
        }
        throw new Error(`Database dump failed with exit code ${exitCode}`);
      }

      // Show any warnings even on success
      const warnings = filterStderr(stderrOutput);
      if (warnings.length > 0) {
        console.log(chalk.yellow(`  Warning: ${warnings[0].substring(0, 200)}`));
      }

      // Check dump file size
      const stats = fs.statSync(tempFile);
      const sizeMB = (stats.size / 1024 / 1024).toFixed(2);
      console.log(chalk.gray(`  Dump complete (${sizeMB} MB)`));

      if (stats.size < 1000) {
        // Less than 1KB is suspicious
        const content = fs.readFileSync(tempFile, "utf8").substring(0, 500);
        throw new Error(`Dump file is suspiciously small (${stats.size} bytes). Content: ${content}`);
      }

      // Step 2: Import to local database via Docker
      console.log(chalk.gray("  Importing to local database..."));
      exec(
        `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' ${LOCAL_DATABASE.database} < "${tempFile}"`,
        debug,
      );
    } finally {
      // Unregister signal handlers
      process.removeListener("SIGINT", signalHandler);
      process.removeListener("SIGTERM", signalHandler);

      // Keep the dump file for debugging/re-import attempts
      // User can manually delete it after successful import
      if (fs.existsSync(tempFile)) {
        console.log(chalk.gray(`  Dump file preserved: ${tempFile}`));
      }
    }
  }

  /**
   * Wait for a child process to exit.
   */
  private waitForProcess(proc: ChildProcess): Promise<number> {
    return new Promise<number>((resolve) => {
      proc.on("close", (code) => {
        resolve(code ?? 0);
      });
      proc.on("error", (err) => {
        debug("Process error: %s", err.message);
        resolve(1);
      });
    });
  }

  /**
   * Verify the local database has tables after restore.
   */
  private verifyLocalDatabase(): number {
    const result = exec(
      `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' ${LOCAL_DATABASE.database} -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${LOCAL_DATABASE.database}'"`,
      debug,
    );
    return parseInt(result.stdout.trim(), 10) || 0;
  }

  /**
   * Delete queue entries older than the specified date.
   */
  private filterQueueByDate(): void {
    if (!this.options.tableFilters.queueDateFilter) {
      return;
    }

    const dateStr = this.options.tableFilters.queueDateFilter.toISOString().split("T")[0];

    exec(
      `docker compose exec -T ${LOCAL_DATABASE.dockerContainer} mysql -u ${LOCAL_DATABASE.user} -p'${LOCAL_DATABASE.password}' ${LOCAL_DATABASE.database} -e "DELETE FROM queue WHERE created_on < '${dateStr}'"`,
      debug,
    );
  }
}
