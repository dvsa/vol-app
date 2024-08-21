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

program
  .description("Script to refresh the local VOL application")
  .option(
    "-i, --no-interaction",
    "Run in non-interactive mode; this will execute the following: composer install, copy app dist files (all), reset database (full), create liquibase.properties file, flush redis, and reset ldap.",
  )
  .action(async (options) => {
    const actions = await Promise.all(
      fs
        .readdirSync(path.resolve(__dirname, "actions"))
        .filter((file) => file.endsWith(".ts") && !file.endsWith("Interface.ts"))
        .map((file) => import(`./actions/${file}`)),
    );

    const isActionInterface = (action: any): action is ActionInterface => {
      return "prompt" in action && "execute" in action;
    };

    for (const action of actions) {
      const instance = new action.default();

      if (isActionInterface(instance) === false) {
        console.warn(chalk.red(`Error: ${instance.name} does not implement ActionInterface`));
        continue;
      }

      const shouldRun = await instance.prompt(!options.interaction);

      if (shouldRun) {
        try {
          await instance.execute(progressBarFactory());
        } catch (e: unknown) {
          if (e instanceof Error) {
            console.error(`\n\n${chalk.red(e.message)}\n`);
          }
        }
      }
    }

    const hostsFile = fs.readFileSync("/etc/hosts", "utf8");

    if (!hostsFile.includes("local.olcs.dev-dvsacloud.uk")) {
      console.warn(chalk.yellow(`/etc/hosts has not been updated with the local domains. Please run:`));
      console.warn(
        chalk.bgYellow(
          `sudo echo "127.0.0.1 iuweb.local.olcs.dev-dvsacloud.uk ssweb.local.olcs.dev-dvsacloud.uk api.local.olcs.dev-dvsacloud.uk cdn.local.olcs.dev-dvsacloud.uk" >> /etc/hosts`,
        ),
      );
    }

    process.exit(0);
  });

program.parse(process.argv);

process.on("unhandledRejection", (err) => {
  console.error(`\n\nUncaught Error: ${chalk.red(err)}\n\n`);

  process.exit(1);
});
