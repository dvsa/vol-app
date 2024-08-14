import prompts from "prompts";
import shell from "shelljs";
import path from "path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";

const debug = createDebug("refresh:actions:ComposerInstall");

const phpAppDirectoryNames = ["api", "selfserve", "internal"];
const phpAppDirectories = phpAppDirectoryNames.map((dir) => path.resolve(__dirname, `../../../app/${dir}`));

export default class ComposerInstall implements ActionInterface {
  async prompt(): Promise<boolean> {
    const isComposerInstalled = shell.exec("composer --version", { silent: !debug.enabled }).code === 0;

    if (!isComposerInstalled) {
      console.error(chalk.red("Error: Composer is not installed. Skipping Composer install..."));
      return false;
    }

    const response = await prompts({
      type: "confirm",
      name: "composer-install",
      message: "Install Composer dependencies?",
    });

    return response["composer-install"];
  }

  async execute(): Promise<void> {
    phpAppDirectories.forEach((dir) => {
      debug(chalk.blue(`Running composer install in ${dir}...`));

      if (
        shell.exec("composer install --no-interaction --no-progress", {
          cwd: dir,
          silent: !debug.enabled,
          env: {
            ...process.env,
            FORCE_COLOR: "1",
          },
        }).code !== 0
      ) {
        console.error(chalk.red(`Error: Composer install failed in ${dir}`));
      }
    });
  }
}
