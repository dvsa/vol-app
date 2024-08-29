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
  private ignorePlatformReqs: boolean = false;

  async prompt(): Promise<boolean> {
    const { shouldInstall } = await prompts({
      type: "confirm",
      name: "shouldInstall",
      message: "Install Composer dependencies?",
    });

    if (shouldInstall) {
      const { ignoreReqs } = await prompts({
        type: "confirm",
        name: "ignoreReqs",
        message: "Do you want to add the --ignore-platform-reqs flag?",
        initial: false,
      });

      this.ignorePlatformReqs = ignoreReqs;
    }

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

      const command = `composer install ${this.ignorePlatformReqs ? '--ignore-platform-reqs' : ''} --no-interaction --no-progress`;

      exec(command, debug, {
        cwd: dir,
      });

      progress.increment();
    });

    progress.stop();
  }
}
