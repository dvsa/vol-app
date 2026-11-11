import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import createDebug from "debug";
import shell from "shelljs";
import { GenericBar } from "cli-progress";
import ActionInterface from "./ActionInterface";
import exec from "../exec";

const debug = createDebug("refresh:actions:DevelopmentSettings");

const repositoryRoot = path.resolve(__dirname, "../../../../");
const phpAppDirectoryNames = ["api", "selfserve", "internal"];

// What each setting is when neither the root .env file nor the shell sets it
export const defaults = {
  XDEBUG_MODE: "off",
  VOL_CONFIG_CACHE: "1",
  VOL_DEVTOOLS: "0",
};

export type SettingName = keyof typeof defaults;
export type Settings = Record<SettingName, string>;

const settingNames = Object.keys(defaults) as SettingName[];
const xdebugModes = ["off", "debug", "coverage", "debug,develop"];

// A service that compose.yaml passes all of the settings to
const serviceWithEverySetting = "selfserve";

// A line that sets a variable in the .env file: an optional "export", the name, then the rest of the line
const envLinePattern = /^(\s*(?:export\s+)?)([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$/;

const isOn = (value: string): boolean => ["1", "true", "yes", "on"].includes(value.toLowerCase());
const onOff = (value: string): string => (isOn(value) ? "on" : "off");

/**
 * The settings the containers are started with, leaving out any that are empty. Docker Compose is asked
 * for them, because it works them out from compose.yaml, the .env file and the shell, and reading the
 * .env file here would have to match the way Compose reads it.
 */
export const settingsFromCompose = (projectDirectory: string): Partial<Settings> => {
  // Not logged, because the output includes every container's environment
  const result = shell.exec("docker compose config --format json", { silent: true, cwd: projectDirectory });

  if (result.code !== 0) {
    throw new Error(`docker compose config failed: ${result.stderr}`);
  }

  const environment = JSON.parse(result.stdout).services?.[serviceWithEverySetting]?.environment ?? {};
  const settings: Partial<Settings> = {};

  for (const name of settingNames) {
    if (typeof environment[name] === "string" && environment[name] !== "") {
      settings[name] = environment[name];
    }
  }

  return settings;
};

/**
 * What follows the value on a line of the .env file, which is a comment if anything. A quoted value ends
 * at its closing quote, and an unquoted one where " #" starts a comment.
 */
const afterValue = (text: string): string => {
  const quoted = text.match(/^(["']).*?\1(.*)$/);

  if (quoted) {
    return quoted[2];
  }

  const commentStart = text.indexOf(" #");

  return commentStart === -1 ? "" : text.slice(commentStart);
};

/**
 * The contents of the .env file with the settings replaced, keeping any "export" or comment on their
 * lines. Settings it doesn't have yet are added at the end, and every other line is left as it was.
 */
export const withSettings = (contents: string, settings: Settings): string => {
  const lines = contents === "" ? [] : contents.replace(/\r?\n$/, "").split(/\r?\n/);
  const missing = new Set<SettingName>(settingNames);

  const updated = lines.map((line) => {
    const match = line.match(envLinePattern);

    if (!match || !(settingNames as string[]).includes(match[2])) {
      return line;
    }

    const [, prefix, name, rest] = match;
    missing.delete(name as SettingName);

    // The value is written unquoted, and after an unquoted value only " #" starts a comment, so a
    // comment that followed a closing quote straight away needs a space before it
    const comment = afterValue(rest);
    const separator = comment === "" || /^\s/.test(comment) ? "" : " ";

    return `${prefix}${name}=${settings[name as SettingName]}${separator}${comment}`;
  });

  if (missing.size > 0) {
    if (updated.length > 0 && updated[updated.length - 1] !== "") {
      updated.push("");
    }

    updated.push("# Development settings, see docs/app/local-setup");
    missing.forEach((name) => updated.push(`${name}=${settings[name]}`));
  }

  return updated.join("\n") + "\n";
};

/**
 * Sets the development toggles (Xdebug, configuration cache, developer toolbar) in the root .env
 * file, which Docker Compose reads, and recreates the PHP containers so they take effect.
 */
export default class DevelopmentSettings implements ActionInterface {
  private settings: Settings = { ...defaults };
  private recreateContainers = false;
  private readonly envFile: string;

  /**
   * @param projectDirectory where compose.yaml and the .env file are
   */
  constructor(private readonly projectDirectory: string = repositoryRoot) {
    this.envFile = path.join(projectDirectory, ".env");
  }

  async prompt(): Promise<boolean> {
    const current = this.currentSettings();
    const summary = [
      `Xdebug ${current.XDEBUG_MODE}`,
      `config cache ${onOff(current.VOL_CONFIG_CACHE)}`,
      `developer toolbar ${onOff(current.VOL_DEVTOOLS)}`,
    ].join(", ");

    const { shouldChange } = await prompts({
      type: "confirm",
      name: "shouldChange",
      message: `Change the development settings? (${summary})`,
      initial: false,
    });

    if (!shouldChange) {
      return false;
    }

    const modes = xdebugModes.includes(current.XDEBUG_MODE) ? xdebugModes : [...xdebugModes, current.XDEBUG_MODE];

    const answers = await prompts([
      {
        type: "select",
        name: "xdebugMode",
        message: "Xdebug mode (anything but off slows every request down)",
        choices: modes.map((mode) => ({ title: mode, value: mode })),
        initial: modes.indexOf(current.XDEBUG_MODE),
      },
      {
        type: "toggle",
        name: "configCache",
        message: "Cache the merged Laminas configuration?",
        initial: isOn(current.VOL_CONFIG_CACHE),
        active: "on",
        inactive: "off",
      },
      {
        type: "toggle",
        name: "devTools",
        message: "Load the Laminas developer toolbar in selfserve and internal?",
        initial: isOn(current.VOL_DEVTOOLS),
        active: "on",
        inactive: "off",
      },
      {
        type: "confirm",
        name: "recreateContainers",
        message: "Recreate the PHP containers so the settings take effect?",
        initial: true,
      },
    ]);

    if (answers.recreateContainers === undefined) {
      return false;
    }

    this.settings = {
      XDEBUG_MODE: answers.xdebugMode,
      VOL_CONFIG_CACHE: answers.configCache ? "1" : "0",
      VOL_DEVTOOLS: answers.devTools ? "1" : "0",
    };
    this.recreateContainers = answers.recreateContainers;

    this.warnAboutIgnoredSettings();

    return true;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(this.recreateContainers ? 2 : 1, 0);

    debug(chalk.blue(`Writing ${JSON.stringify(this.settings)} to ${this.envFile}`));
    const contents = fs.existsSync(this.envFile) ? fs.readFileSync(this.envFile, "utf8") : "";
    fs.writeFileSync(this.envFile, withSettings(contents, this.settings));
    progress.increment();

    if (this.recreateContainers) {
      // Compose recreates only the containers whose configuration has changed
      exec("docker compose up -d api cli selfserve internal", debug, { cwd: this.projectDirectory });
      progress.increment();
    }

    progress.stop();
  }

  /**
   * The settings as they are now, to start the questions from
   */
  private currentSettings(): Settings {
    try {
      return { ...defaults, ...settingsFromCompose(this.projectDirectory) };
    } catch (error) {
      debug(chalk.red(String(error)));
      console.warn(
        chalk.yellow("Docker Compose couldn't be asked for the development settings, so their defaults are shown."),
      );

      return { ...defaults };
    }
  }

  private warnAboutIgnoredSettings(): void {
    settingNames
      .filter((name) => process.env[name] !== undefined)
      .forEach((name) =>
        console.warn(chalk.yellow(`${name} is set in your shell, which takes precedence over the .env file.`)),
      );

    // The configuration cache and toolbar settings are read by each app's development.config.php
    for (const app of phpAppDirectoryNames) {
      const developmentConfig = path.join(this.projectDirectory, "app", app, "config", "development.config.php");
      const name = `app/${app}/config/development.config.php`;

      if (!fs.existsSync(developmentConfig)) {
        console.warn(
          chalk.yellow(`${name} doesn't exist, so the config cache and toolbar settings don't apply to ${app}.`),
        );
      } else if (!fs.readFileSync(developmentConfig, "utf8").includes("DevelopmentConfig")) {
        console.warn(
          chalk.yellow(
            `${name} is an older copy of its dist file, so these settings may not apply to ${app}. ` +
              `Copy it again with the "Copy the Laminas configuration dist files" step.`,
          ),
        );
      }
    }
  }
}
