#!/usr/bin/env ts-node

import { program } from "commander";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import cliProgress from "cli-progress";
import ActionInterface from "./actions/ActionInterface";

const progressBarFactory = () => {
  return new cliProgress.Bar(
    {
      clearOnComplete: true,
    },
    cliProgress.Presets.shades_classic,
  );
};

// Define explicit action order
const actionOrder = [
  "ComposerInstall",
  "CopyAppDistFiles",
  "SyncAwsSecretsAndParameters",
  "CopyDockerComposeDist",
  "FlushRedis",
  "ResetDatabase",
  "DownloadRemoteDatabase",
  "ResetLdap",
];

/**
 * Load an action by name, supporting both single-file and folder-based patterns:
 * - Single file: actions/ActionName.ts
 * - Folder module: actions/ActionName/index.ts
 */
async function loadAction(actionName: string): Promise<any> {
  const actionsDir = path.join(__dirname, "actions");
  const singleFilePath = path.join(actionsDir, `${actionName}.ts`);
  const folderIndexPath = path.join(actionsDir, actionName, "index.ts");

  // Check if single file exists
  if (fs.existsSync(singleFilePath)) {
    return await import(`./actions/${actionName}.ts`);
  }

  // Check if folder module exists
  if (fs.existsSync(folderIndexPath)) {
    return await import(`./actions/${actionName}/index.ts`);
  }

  // Check if it's a compiled .js file (for compatibility)
  const singleFileJs = path.join(actionsDir, `${actionName}.js`);
  if (fs.existsSync(singleFileJs)) {
    return await import(`./actions/${actionName}.js`);
  }

  throw new Error(`Action file not found: ${actionName}`);
}

program.description("Script to refresh the local VOL application").action(async () => {
  const isActionInterface = (action: any): action is ActionInterface => {
    return "prompt" in action && "execute" in action;
  };

  // Load actions in the specified order
  for (const actionName of actionOrder) {
    try {
      const actionModule = await loadAction(actionName);
      const instance = new actionModule.default();

      if (isActionInterface(instance) === false) {
        console.warn(chalk.red(`Error: ${actionName} does not implement ActionInterface`));
        continue;
      }

      const shouldRun = await instance.prompt();

      if (shouldRun) {
        try {
          await instance.execute(progressBarFactory());
        } catch (e: unknown) {
          if (e instanceof Error) {
            console.error(`\n\n${chalk.red(e.message)}\n`);
          }
        }
      }
    } catch (importError) {
      console.warn(
        chalk.yellow(
          `Warning: Could not load action '${actionName}': ${
            importError instanceof Error ? importError.message : String(importError)
          }`,
        ),
      );
      continue;
    }
  }

  process.exit(0);
});

program.parse(process.argv);

process.on("unhandledRejection", (err) => {
  console.error(`\n\nUncaught Error: ${chalk.red(err)}\n\n`);

  process.exit(1);
});
