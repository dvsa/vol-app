import prompts from "prompts";
import fs from "node:fs";
import path from "path";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";

const debug = createDebug("refresh:actions:CopyAppDistFiles");

const phpAppDirectoryNames = ["api", "selfserve", "internal"];
const phpAppDirectories = phpAppDirectoryNames.map((dir) => path.resolve(__dirname, `../../../app/${dir}`));

export default class ResetDatabase implements ActionInterface {
  filesToCopy: string[] = [];

  async prompt(): Promise<boolean> {
    const response = await prompts({
      type: "confirm",
      name: "should-copy",
      message: "Copy the Laminas configuration dist files?",
      warn: "This will overwrite existing configuration files.",
    });

    if (!response["should-copy"]) {
      return false;
    }

    // Get environment variable for ETL directory or default to ../../../../olcs-etl
    const etlDirectory = process.env.OLCS_ETL_DIR || "../../../../olcs-etl";

    phpAppDirectories.push(path.resolve(__dirname, etlDirectory));

    const appConfigDistFiles = phpAppDirectories
      .map((dir) => {
        let configDir = dir;
        if (fs.existsSync(path.join(dir, "config"))) {
          configDir = path.join(dir, "config");
        }

        return fs
          .readdirSync(configDir, { recursive: true })
          .filter((file) => typeof file === "string")
          .map((fileName) => {
            return path.join(configDir, fileName);
          })
          .filter((fileName) => fs.lstatSync(fileName).isFile())
          .filter((fileName) => fileName.endsWith(".dist"));
      })
      .flat();

    this.filesToCopy = (
      await prompts({
        type: "multiselect",
        name: "files",
        message: "Which config files do you want to copy?",
        choices: appConfigDistFiles.map((file) => ({ title: file, value: file })),
        hint: "- Space to select. Return to submit",
      })
    ).files;

    return this.filesToCopy.length > 0;
  }

  async execute(): Promise<void> {
    this.filesToCopy.forEach((file) => {
      const destination = file.replace(".dist", "");

      debug(chalk.greenBright(`Copying ${file} to ${destination}...`));

      fs.copyFileSync(file, destination);
    });
  }
}
