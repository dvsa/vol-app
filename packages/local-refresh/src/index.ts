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
  "ResetLdap",
];

program.description("Script to refresh the local VOL application").action(async () => {
  const isActionInterface = (action: any): action is ActionInterface => {
    return "prompt" in action && "execute" in action;
  };

  // Load actions in the specified order
  for (const actionName of actionOrder) {
    try {
      const actionModule = await import(`./actions/${actionName}.ts`);
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
      console.warn(chalk.yellow(`Warning: Could not load action '${actionName}': ${importError}`));
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
