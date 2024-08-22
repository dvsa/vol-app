import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:CopyAppDistFiles");

const phpAppDirectoryNames = ["api", "selfserve", "internal"];
const phpAppDirectories = phpAppDirectoryNames.map((dir) => path.resolve(__dirname, `../../../../app/${dir}`));

export default class CopyAppDistFiles implements ActionInterface {
  filesToCopy: string[] = [];

  async prompt(noInteraction: boolean): Promise<boolean> {
    if (!noInteraction) {
      const { shouldCopy } = await prompts({
        type: "confirm",
        name: "shouldCopy",
        message: "Copy the Laminas configuration dist files?",
        warn: "This will overwrite existing configuration files.",
      });

      if (!shouldCopy) {
        return false;
      }
    }

    let appConfigDistFiles: Map<string, string> = new Map();

    for (const dir of phpAppDirectories) {
      const configDir = path.join(dir, "config");

      if (!fs.existsSync(configDir)) {
        continue;
      }

      const files = fs
        .readdirSync(configDir, { recursive: true })
        .filter((file) => typeof file === "string")
        .map((fileName) => {
          return path.join(configDir, fileName);
        })
        .filter((fileName) => fs.lstatSync(fileName).isFile())
        .filter((fileName) => fileName.endsWith(".dist"));

      files.forEach((file) => {
        const truncatedPath = file.replace(path.dirname(dir), "");

        appConfigDistFiles.set(file, truncatedPath);
      });
    }

    if (noInteraction) {
      this.filesToCopy = Array.from(appConfigDistFiles.keys());
      return this.filesToCopy.length > 0;
    }

    const { files } = await prompts({
      type: "multiselect",
      name: "files",
      message: "Which config files do you want to copy?",
      choices: Array.from(appConfigDistFiles.keys()).map((file) => ({
        title: appConfigDistFiles.get(file) || file,
        value: file,
      })),
      hint: "- Space to select. Return to submit",
    });

    if (!files) {
      return false;
    }

    this.filesToCopy = files;

    return this.filesToCopy.length > 0;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(this.filesToCopy.length, 0);

    this.filesToCopy.forEach((file) => {
      const destination = file.replace(".dist", "");

      debug(chalk.greenBright(`Copying ${file} to ${destination}...`));

      fs.copyFileSync(file, destination);

      progress.increment();
    });

    progress.stop();
  }
}
