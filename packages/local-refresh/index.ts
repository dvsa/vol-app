#!/usr/bin/env ts-node

import { program } from "commander";
import fs from "node:fs";
import path from "path";
import chalk from "chalk";
import ActionInterface from "./actions/ActionInterface";

program.description("Reset the VOL local environment.").action(async () => {
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

    const shouldRun = await instance.prompt();

    if (shouldRun) {
      console.info(`Running action: ${instance.constructor.name}`);
      await instance.execute();
    }
  }

  const hostsFile = fs.readFileSync("/etc/hosts", "utf8");

  if (!hostsFile.includes("local.olcs.dev-dvsacloud.uk")) {
    console.warn(chalk.yellow(`/etc/hosts has not been updated with local domains. Please run:`));
    console.warn(
      chalk.bgYellow(
        `sudo echo "127.0.0.1 iuweb.local.olcs.dev-dvsacloud.uk ssweb.local.olcs.dev-dvsacloud.uk api.local.olcs.dev-dvsacloud.uk cdn.local.olcs.dev-dvsacloud.uk" >> /etc/hosts`,
      ),
    );

    return;
  }

  console.info(chalk.greenBright("Local environment reset complete."));
});

program.parse(process.argv);

process.on("unhandledRejection", (err) => {
  process.exit(1);
});
