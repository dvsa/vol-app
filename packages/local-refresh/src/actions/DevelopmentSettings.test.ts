import { after, before, describe, test } from "node:test";
import assert from "node:assert/strict";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import shell from "shelljs";
import { defaults, Settings, settingsFromCompose, withSettings } from "./DevelopmentSettings";

const repositoryRoot = path.resolve(__dirname, "../../../../");
const settingNames = Object.keys(defaults) as (keyof Settings)[];

const everythingChanged: Settings = { XDEBUG_MODE: "debug", VOL_CONFIG_CACHE: "0", VOL_DEVTOOLS: "1" };

// The ways a .env file can already set, or not set, the settings
const envFiles: Record<string, string> = {
  "no file": "",
  "other variables only": "AWS_PROFILE=vol\n\n# A comment\nCOMPOSE_PROJECT_NAME=vol-app\n",
  "plain values": "XDEBUG_MODE=off\nVOL_CONFIG_CACHE=1\nVOL_DEVTOOLS=0\n",
  "empty values": "XDEBUG_MODE=\nVOL_CONFIG_CACHE=\nVOL_DEVTOOLS=\n",
  "quoted values": 'XDEBUG_MODE="off"\nVOL_CONFIG_CACHE=\'1\'\nVOL_DEVTOOLS="0"\n',
  "a comment after each value": "XDEBUG_MODE=off # slow\nVOL_CONFIG_CACHE=1 # keep on\nVOL_DEVTOOLS=0 # toolbar\n",
  "a comment straight after a closing quote": "XDEBUG_MODE=\"off\"# slow\nVOL_CONFIG_CACHE='1'# keep on\n",
  "exported values": "export XDEBUG_MODE=off\nexport VOL_CONFIG_CACHE=1\n",
  "spaces around the equals sign": "XDEBUG_MODE = off\nVOL_CONFIG_CACHE = 1\n",
  "a setting on two lines": "VOL_CONFIG_CACHE=1\nAWS_PROFILE=vol\nVOL_CONFIG_CACHE=\n",
  "no final newline": "AWS_PROFILE=vol\nVOL_DEVTOOLS=0",
  "Windows line endings": "AWS_PROFILE=vol\r\nVOL_DEVTOOLS=0\r\n",
};

describe("withSettings", () => {
  test("adds the settings to an empty file under a heading", () => {
    assert.equal(
      withSettings("", everythingChanged),
      "# Development settings, see docs/app/local-setup\nXDEBUG_MODE=debug\nVOL_CONFIG_CACHE=0\nVOL_DEVTOOLS=1\n",
    );
  });

  test("adds only the settings the file doesn't have, after a blank line", () => {
    assert.equal(
      withSettings("AWS_PROFILE=vol\nVOL_DEVTOOLS=0", everythingChanged),
      "AWS_PROFILE=vol\nVOL_DEVTOOLS=1\n\n" +
        "# Development settings, see docs/app/local-setup\nXDEBUG_MODE=debug\nVOL_CONFIG_CACHE=0\n",
    );
  });

  test("replaces the values in place and leaves every other line as it was", () => {
    assert.equal(
      withSettings(
        "# Mine\nAWS_PROFILE=vol\nXDEBUG_MODE=off\n\nVOL_CONFIG_CACHE=1\nVOL_DEVTOOLS=0\nOTHER=1 # note\n",
        everythingChanged,
      ),
      "# Mine\nAWS_PROFILE=vol\nXDEBUG_MODE=debug\n\nVOL_CONFIG_CACHE=0\nVOL_DEVTOOLS=1\nOTHER=1 # note\n",
    );
  });

  test("keeps an export and a comment on a setting's line", () => {
    assert.equal(
      withSettings("export XDEBUG_MODE=off # slow\nVOL_CONFIG_CACHE=1\nVOL_DEVTOOLS=0\n", everythingChanged),
      "export XDEBUG_MODE=debug # slow\nVOL_CONFIG_CACHE=0\nVOL_DEVTOOLS=1\n",
    );
  });

  test("writes a quoted value unquoted, with a space before a comment that followed the quote", () => {
    assert.equal(
      withSettings('XDEBUG_MODE="off"# slow\nVOL_CONFIG_CACHE=\'1\'\nVOL_DEVTOOLS="0" # toolbar\n', everythingChanged),
      "XDEBUG_MODE=debug # slow\nVOL_CONFIG_CACHE=0\nVOL_DEVTOOLS=1 # toolbar\n",
    );
  });

  test("replaces every line that sets a setting", () => {
    assert.equal(
      withSettings("VOL_CONFIG_CACHE=1\nVOL_CONFIG_CACHE=\nXDEBUG_MODE=off\nVOL_DEVTOOLS=0\n", everythingChanged),
      "VOL_CONFIG_CACHE=0\nVOL_CONFIG_CACHE=0\nXDEBUG_MODE=debug\nVOL_DEVTOOLS=1\n",
    );
  });

  test("leaves a file that already has the settings unchanged", () => {
    const contents = "export XDEBUG_MODE=debug # slow\nVOL_CONFIG_CACHE=0\nVOL_DEVTOOLS=1\n";

    assert.equal(withSettings(contents, everythingChanged), contents);
  });
});

// These run Docker Compose against the repository's compose.yaml, so they also check that the defaults
// here are the ones compose.yaml gives the containers
const hasCompose = shell.exec("docker compose version", { silent: true }).code === 0;

describe("settingsFromCompose", { skip: hasCompose ? false : "Docker Compose isn't installed" }, () => {
  let projectDirectory: string;
  const shellValues: Partial<Record<keyof Settings, string>> = {};

  const withEnvFile = (contents: string): Settings => {
    const envFile = path.join(projectDirectory, ".env");

    if (contents === "") {
      fs.rmSync(envFile, { force: true });
    } else {
      fs.writeFileSync(envFile, contents);
    }

    return { ...defaults, ...settingsFromCompose(projectDirectory) };
  };

  before(() => {
    projectDirectory = fs.mkdtempSync(path.join(os.tmpdir(), "development-settings-"));
    fs.copyFileSync(path.join(repositoryRoot, "compose.yaml"), path.join(projectDirectory, "compose.yaml"));

    // A setting in the shell would take precedence over the .env file
    for (const name of settingNames) {
      shellValues[name] = process.env[name];
      delete process.env[name];
    }
  });

  after(() => {
    fs.rmSync(projectDirectory, { recursive: true, force: true });

    for (const name of settingNames) {
      if (shellValues[name] !== undefined) {
        process.env[name] = shellValues[name];
      }
    }
  });

  test("gives the defaults when there is no .env file", () => {
    assert.deepEqual(withEnvFile(""), defaults);
  });

  test("gives the default for a setting that is empty", () => {
    assert.deepEqual(withEnvFile(envFiles["empty values"]), defaults);
    assert.deepEqual(withEnvFile(envFiles["a setting on two lines"]), defaults);
  });

  test("reads values the way Compose does", () => {
    assert.deepEqual(withEnvFile("XDEBUG_MODE=debug # slow\nVOL_CONFIG_CACHE='0'# off\nexport VOL_DEVTOOLS=\"1\"\n"), {
      XDEBUG_MODE: "debug",
      VOL_CONFIG_CACHE: "0",
      VOL_DEVTOOLS: "1",
    });
  });

  test("gives a value set in the shell rather than the one in the .env file", () => {
    process.env.VOL_DEVTOOLS = "1";

    try {
      assert.equal(withEnvFile("VOL_DEVTOOLS=0\n").VOL_DEVTOOLS, "1");
    } finally {
      delete process.env.VOL_DEVTOOLS;
    }
  });

  test("fails when Compose can't read the project", () => {
    assert.throws(() => settingsFromCompose(path.join(projectDirectory, "missing")), /docker compose config failed/);
  });

  for (const [description, contents] of Object.entries(envFiles)) {
    for (const settings of [everythingChanged, defaults]) {
      test(`Compose reads back ${JSON.stringify(settings)} written to a file with ${description}`, () => {
        assert.deepEqual(withEnvFile(withSettings(contents, settings)), settings);
      });
    }
  }
});
