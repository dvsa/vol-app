import prompts from "prompts";
import fs from "node:fs";
import path from "node:path";
import chalk from "chalk";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import ActionInterface from "./ActionInterface";
import exec from "../exec";

const debug = createDebug("refresh:actions:DevelopmentSettings");

const repositoryRoot = path.resolve(__dirname, "../../../../");
const phpAppDirectoryNames = ["api", "selfserve", "internal"];

// The defaults compose.yaml uses when the root .env file doesn't set a value
const defaults = {
  XDEBUG_MODE: "off",
  VOL_CONFIG_CACHE: "1",
  VOL_DEVTOOLS: "0",
};

type SettingName = keyof typeof defaults;
type Settings = Record<SettingName, string>;

const settingNames = Object.keys(defaults) as SettingName[];
const xdebugModes = ["off", "debug", "coverage", "debug,develop"];

// A line that sets a variable in the .env file: an optional "export", the name, then the rest of the line
const envLinePattern = /^(\s*(?:export\s+)?)([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$/;

/**
 * Splits what follows the = the way Docker Compose reads it: a quoted value is taken as it is, and an
 * unquoted one ends where " #" starts a comment
 */
const splitEnvValue = (text: string): { value: string; comment: string } => {
  const quoted = text.match(/^(["'])(.*?)\1(.*)$/);

  if (quoted) {
    return { value: quoted[2], comment: quoted[3] };
  }

  const commentStart = text.indexOf(" #");

  return commentStart === -1
    ? { value: text, comment: "" }
    : { value: text.slice(0, commentStart).trimEnd(), comment: text.slice(commentStart) };
};

const isOn = (value: string): boolean => ["1", "true", "yes", "on"].includes(value.toLowerCase());
const onOff = (value: string): string => (isOn(value) ? "on" : "off");

/**
 * Sets the development toggles (Xdebug, configuration cache, developer toolbar) in the root .env
 * file, which Docker Compose reads, and recreates the PHP containers so they take effect.
 */
export default class DevelopmentSettings implements ActionInterface {
  private settings: Settings = { ...defaults };
  private recreateContainers = false;

  constructor(private readonly envFile: string = path.join(repositoryRoot, ".env")) {}

  async prompt(): Promise<boolean> {
    const current: Settings = { ...defaults, ...this.readSettings() };

    const { shouldChange } = await prompts({
      type: "confirm",
      name: "shouldChange",
      message: `Change the development settings? (Xdebug ${current.XDEBUG_MODE}, config cache ${onOff(current.VOL_CONFIG_CACHE)}, developer toolbar ${onOff(current.VOL_DEVTOOLS)})`,
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
    this.writeSettings();
    progress.increment();

    if (this.recreateContainers) {
      // Compose recreates only the containers whose configuration has changed
      exec("docker compose up -d api cli selfserve internal", debug, { cwd: repositoryRoot });
      progress.increment();
    }

    progress.stop();
  }

  /**
   * The development settings in the .env file; other lines are ignored
   */
  private readSettings(): Partial<Settings> {
    if (!fs.existsSync(this.envFile)) {
      return {};
    }

    const settings: Partial<Settings> = {};

    for (const line of fs.readFileSync(this.envFile, "utf8").split(/\r?\n/)) {
      const match = line.match(envLinePattern);

      if (match && (settingNames as string[]).includes(match[2])) {
        settings[match[2] as SettingName] = splitEnvValue(match[3]).value;
      }
    }

    return settings;
  }

  /**
   * Replaces the development settings in the .env file, keeping any "export" or comment on their
   * lines, appends any it doesn't have yet, and leaves every other line as it was
   */
  private writeSettings(): void {
    const contents = fs.existsSync(this.envFile) ? fs.readFileSync(this.envFile, "utf8") : "";
    const lines = contents === "" ? [] : contents.replace(/\r?\n$/, "").split(/\r?\n/);
    const missing = new Set<SettingName>(settingNames);

    const updated = lines.map((line) => {
      const match = line.match(envLinePattern);

      if (!match || !(settingNames as string[]).includes(match[2])) {
        return line;
      }

      const [, prefix, name, rest] = match;
      missing.delete(name as SettingName);

      return `${prefix}${name}=${this.settings[name as SettingName]}${splitEnvValue(rest).comment}`;
    });

    if (missing.size > 0) {
      if (updated.length > 0 && updated[updated.length - 1] !== "") {
        updated.push("");
      }

      updated.push("# Development settings, see docs/app/local-setup");
      missing.forEach((name) => updated.push(`${name}=${this.settings[name]}`));
    }

    fs.writeFileSync(this.envFile, updated.join("\n") + "\n");
  }

  private warnAboutIgnoredSettings(): void {
    settingNames
      .filter((name) => process.env[name] !== undefined)
      .forEach((name) =>
        console.warn(chalk.yellow(`${name} is set in your shell, which takes precedence over the .env file.`)),
      );

    // The configuration cache and toolbar settings are read by each app's development.config.php
    for (const app of phpAppDirectoryNames) {
      const developmentConfig = path.join(repositoryRoot, "app", app, "config", "development.config.php");

      if (!fs.existsSync(developmentConfig)) {
        console.warn(
          chalk.yellow(
            `app/${app} has no config/development.config.php, so the config cache and toolbar settings don't apply to it.`,
          ),
        );
      } else if (!fs.readFileSync(developmentConfig, "utf8").includes("VOL_CONFIG_CACHE")) {
        console.warn(
          chalk.yellow(
            `app/${app}/config/development.config.php predates these settings, so they don't apply to it. Copy its dist file with the "Copy the Laminas configuration dist files" step.`,
          ),
        );
      }
    }
  }
}
