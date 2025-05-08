import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:CopyDockerComposeDist");

export default class CopyDockerComposeDist implements ActionInterface {
  async prompt(): Promise<boolean> {
    const { shouldCopy } = await prompts({
      type: "confirm",
      name: "shouldCopy",
      message: "Copy the compose-custom dist file? (develop using symlinks)",
      warn: "This will overwrite existing compose-custom.yaml",
    });

    return shouldCopy;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(1, 0);

    const file: string = path.join(__dirname, "../../../../compose-custom.yaml.dist");
    const destination: string = file.replace(".dist", "");

    debug(chalk.greenBright(`Copying ${file} to ${destination}...`));

    if (!fs.existsSync(file)) {
      throw new Error(chalk.redBright(`File ${file} does not exist`));
    }

    fs.copyFileSync(file, destination);

    progress.stop();
  }
}
