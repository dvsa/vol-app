import prompts from "prompts";
import exec from "../exec";
import path from "node:path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:ComposerInstall");

const phpAppDirectoryNames = ["api", "selfserve", "internal"];
const phpAppDirectories = phpAppDirectoryNames.map((dir) => path.resolve(__dirname, `../../../../app/${dir}`));

export default class ComposerInstall implements ActionInterface {
  async prompt(noInteraction: boolean): Promise<boolean> {
    if (noInteraction) {
      return true;
    }

    const { shouldInstall } = await prompts({
      type: "confirm",
      name: "shouldInstall",
      message: "Install Composer dependencies?",
    });

    return shouldInstall;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(phpAppDirectories.length, 0);

    try {
      exec("composer --version", debug);
    } catch (e: unknown) {
      throw new Error("Composer is not installed. Please install Composer before running this action.");
    }

    phpAppDirectories.forEach((dir) => {
      debug(chalk.blue(`Running composer install in ${dir}...`));

      exec("composer install --no-interaction --no-progress", debug, {
        cwd: dir,
      });

      progress.increment();
    });

    progress.stop();
  }
}
