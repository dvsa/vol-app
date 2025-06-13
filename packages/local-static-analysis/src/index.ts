#!/usr/bin/env ts-node

import { program } from "commander";
import prompts from "prompts";
import chalk from "chalk";
import cliProgress from "cli-progress";
import shell from "shelljs";
import exec from "./exec";
import createDebug from "debug";

const debug = createDebug("static-analysis:index");

interface Options {
  app?: string;
  tool?: string;
  all?: boolean;
}

const apps = ["api", "selfserve", "internal"] as const;
type App = (typeof apps)[number];

const tools = ["phpstan", "phpcs", "psalm", "phpcbf"] as const;
type Tool = (typeof tools)[number];

const toolCommands: Record<Tool, string> = {
  phpstan: "vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --memory-limit=1048M",
  phpcs: "vendor/bin/phpcs --standard=phpcs.xml.dist",
  psalm: "vendor/bin/psalm",
  phpcbf: "vendor/bin/phpcbf --standard=phpcs.xml.dist",
};

const toolDescriptions: Record<Tool, string> = {
  phpstan: "PHPStan - PHP Static Analysis Tool",
  phpcs: "PHP CodeSniffer - Coding Standards",
  psalm: "Psalm - Static Analysis Tool",
  phpcbf: "PHP Code Beautifier and Fixer",
};

const runTool = (app: App, tool: Tool): boolean => {
  const command = `docker compose exec -T ${app} ${toolCommands[tool]}`;

  try {
    debug(`Running: ${command}`);

    // Run the command without throwing on non-zero exit codes
    const result = shell.exec(command, {
      silent: true,
      env: {
        ...process.env,
        FORCE_COLOR: "1",
      },
    });

    // Always show the output, whether it's stdout or stderr
    if (result.stdout) {
      console.log(result.stdout);
    }

    if (result.stderr && result.stderr.trim()) {
      console.error(result.stderr);
    }

    // Check exit code to determine success/failure
    return result.code === 0;
  } catch (error) {
    if (error instanceof Error) {
      console.error(chalk.red(`\n✗ ${app}: ${tool} failed\n`));
      console.error(error.message);
    }
    return false;
  }
};

const runAnalysis = async (selectedApps: App[], selectedTools: Tool[]) => {
  const totalSteps = selectedApps.length * selectedTools.length;
  const progressBar = new cliProgress.Bar(
    {
      clearOnComplete: true,
      format: "Progress |{bar}| {percentage}% | {value}/{total} Steps | {app} - {tool}",
    },
    cliProgress.Presets.shades_classic,
  );

  progressBar.start(totalSteps, 0, { app: "", tool: "" });
  let step = 0;
  const results: Record<string, boolean> = {};

  for (const app of selectedApps) {
    console.log(chalk.cyan(`\n🔍 Analyzing ${app}...`));

    for (const tool of selectedTools) {
      progressBar.update(step++, { app, tool });

      const success = runTool(app, tool);
      results[`${app}-${tool}`] = success;

      if (success) {
        console.log(chalk.green(`✓ ${app}: ${toolDescriptions[tool]} passed`));
      } else {
        console.log(chalk.red(`✗ ${app}: ${toolDescriptions[tool]} failed`));
      }
    }
  }

  progressBar.stop();

  // Summary
  console.log(chalk.cyan("\n📊 Summary:"));
  const failures = Object.entries(results).filter(([, success]) => !success);

  if (failures.length === 0) {
    console.log(chalk.green("✨ All checks passed!"));
  } else {
    console.log(chalk.red(`❌ ${failures.length} check(s) failed:`));
    failures.forEach(([key]) => {
      const [app, tool] = key.split("-");
      console.log(chalk.red(`  - ${app}: ${tool}`));
    });
  }
};

program
  .description("Run static analysis tools for VOL applications")
  .option("-a, --app <app>", "Specify app to analyze (api, selfserve, internal)")
  .option("-t, --tool <tool>", "Specify tool to run (phpstan, phpcs, psalm, phpcbf)")
  .option("--all", "Run all tools on all apps (non-interactive)")
  .parse(process.argv);

const checkContainersRunning = (): boolean => {
  console.log(chalk.gray("Checking Docker containers..."));

  const requiredContainers = apps;
  const missingContainers: string[] = [];

  for (const container of requiredContainers) {
    const result = shell.exec(`docker compose ps -q ${container}`, { silent: true });

    if (!result.stdout.trim() || result.code !== 0) {
      missingContainers.push(container);
    }
  }

  if (missingContainers.length > 0) {
    console.error(chalk.red("\n⚠️  The following containers are not running:"));
    missingContainers.forEach((container) => {
      console.error(chalk.red(`   - ${container}`));
    });
    console.error(chalk.yellow("\nPlease start the containers with: docker compose up -d\n"));
    return false;
  }

  console.log(chalk.green("✓ All containers are running\n"));
  return true;
};

const main = async () => {
  // Check if containers are running first
  if (!checkContainersRunning()) {
    process.exit(1);
  }

  const options = program.opts<Options>();

  let selectedApps: App[] = [];
  let selectedTools: Tool[] = [];

  // Validate CLI options
  if (options.app && !apps.includes(options.app as App)) {
    console.error(chalk.red(`Invalid app: ${options.app}. Valid options: ${apps.join(", ")}`));
    process.exit(1);
  }

  if (options.tool && !tools.includes(options.tool as Tool)) {
    console.error(chalk.red(`Invalid tool: ${options.tool}. Valid options: ${tools.join(", ")}`));
    process.exit(1);
  }

  // Handle --all flag
  if (options.all) {
    selectedApps = [...apps];
    selectedTools = [...tools];
  }
  // Handle CLI options
  else if (options.app || options.tool) {
    selectedApps = options.app ? [options.app as App] : [...apps];
    selectedTools = options.tool ? [options.tool as Tool] : [...tools];
  }
  // Interactive mode
  else {
    const { app } = await prompts({
      type: "select",
      name: "app",
      message: "What would you like to analyze?",
      choices: [
        { title: "All applications", value: "all" },
        { title: "API only", value: "api" },
        { title: "Selfserve only", value: "selfserve" },
        { title: "Internal only", value: "internal" },
      ],
    });

    if (!app) {
      console.log(chalk.yellow("Operation cancelled"));
      process.exit(0);
    }

    selectedApps = app === "all" ? [...apps] : [app as App];

    const { tool } = await prompts({
      type: "select",
      name: "tool",
      message: "Which tool would you like to run?",
      choices: [
        { title: "All tools", value: "all" },
        { title: toolDescriptions.phpstan, value: "phpstan" },
        { title: toolDescriptions.phpcs, value: "phpcs" },
        { title: toolDescriptions.psalm, value: "psalm" },
        { title: toolDescriptions.phpcbf, value: "phpcbf" },
      ],
    });

    if (!tool) {
      console.log(chalk.yellow("Operation cancelled"));
      process.exit(0);
    }

    selectedTools = tool === "all" ? [...tools] : [tool as Tool];
  }

  await runAnalysis(selectedApps, selectedTools);
};

main().catch((error) => {
  console.error(chalk.red("An error occurred:"), error);
  process.exit(1);
});
