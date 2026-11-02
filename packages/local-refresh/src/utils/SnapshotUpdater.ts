import { spawn } from "node:child_process";
import fs from "node:fs";
import path from "node:path";
import readline from "node:readline";
import chalk from "chalk";
import createDebug from "debug";
import prompts from "prompts";
import { GenericBar } from "cli-progress";
import exec from "../exec";

const repoRoot = path.resolve(__dirname, "../../../..");

export interface SnapshotPackage {
  /** Package directory, relative to the repo root (where `vendor/bin/phpunit` is run). */
  directory: string;
  /** Snapshot directory, relative to the package directory. */
  snapshots: string;
}

/**
 * Reminds you to update the libraries' vendor folders before writing snapshots from them.
 *
 * A package with no committed composer.lock is tested in CI against the newest compatible releases, so snapshots
 * written from an out-of-date vendor folder can fail there. Refresh's Composer step installs only the apps.
 *
 * @return {Promise<boolean>} Whether to go ahead.
 */
export async function confirmInstalledVersions(packages: SnapshotPackage[]): Promise<boolean> {
  const debug = createDebug("refresh:utils:SnapshotUpdater");
  const unlocked = packages
    .map(({ directory }) => directory)
    .filter((directory) => exec(`git ls-files -- ${directory}/composer.lock`, debug, { cwd: repoRoot }).stdout === "");

  if (unlocked.length === 0) {
    return true;
  }

  const one = unlocked.length === 1;

  console.log(
    chalk.yellow(
      `\n${listOf(unlocked)} ${one ? "commits" : "commit"} no composer.lock, so CI tests ${one ? "it" : "them"} against the ` +
        "newest compatible Laminas releases. Snapshots written from an out-of-date vendor folder can fail in CI. " +
        `Run \`composer update\` in ${one ? "it" : "each"} first.\n`,
    ),
  );

  const { shouldContinue } = await prompts({
    type: "confirm",
    name: "shouldContinue",
    message: "Continue with the packages installed now?",
    initial: true,
  });

  return shouldContinue;
}

/**
 * "a", "a and b", "a, b and c".
 */
function listOf(names: string[]): string {
  return names.length === 1 ? names[0] : `${names.slice(0, -1).join(", ")} and ${names[names.length - 1]}`;
}

/**
 * The number of snapshot files committed under a directory, or 0 if it does not exist yet.
 */
function countSnapshots(directory: string): number {
  if (!fs.existsSync(directory)) {
    return 0;
  }

  return fs.readdirSync(directory, { recursive: true }).filter((file) => String(file).endsWith(".json")).length;
}

/**
 * Runs `UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter <test>` with TeamCity output, which PHPUnit writes as it goes:
 * the number of tests it found before the first one runs, then a line as each test finishes.
 *
 * @return {Promise<number | null>} PHPUnit's exit code, or null if a signal stopped it.
 */
function runSnapshotTest(
  testName: string,
  cwd: string,
  debug: createDebug.Debugger,
  onTestCount: (count: number) => void,
  onTestFinished: () => void,
): Promise<number | null> {
  return new Promise((resolve, reject) => {
    const phpunit = spawn("vendor/bin/phpunit", ["--filter", testName, "--teamcity"], {
      cwd,
      env: { ...process.env, UPDATE_SNAPSHOTS: "1" },
    });

    readline.createInterface({ input: phpunit.stdout }).on("line", (line) => {
      debug(line);

      const testCount = line.match(/^##teamcity\[testCount count='(\d+)'/);

      if (testCount) {
        onTestCount(Number(testCount[1]));
      } else if (line.startsWith("##teamcity[testFinished ")) {
        onTestFinished();
      }
    });
    readline.createInterface({ input: phpunit.stderr }).on("line", (line) => debug(line));

    phpunit.on("error", reject);
    phpunit.on("close", resolve);
  });
}

/**
 * Regenerates a snapshot test's committed files in each package, then reports what changed.
 *
 * Runs the same command the tests tell you to run (`UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter <test>`),
 * on the host, using each package's own vendor directory. Every snapshot is its own test, so the progress bar
 * moves one step per snapshot written. The packages run at the same time, so PHPUnit's start-up in each (around a
 * second, before its first test) happens once at the start instead of pausing the bar between packages; they write
 * to separate snapshot directories.
 */
export default async function updateSnapshots(
  testName: string,
  packages: SnapshotPackage[],
  progress: GenericBar,
  debug: createDebug.Debugger,
): Promise<void> {
  const command = `UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter ${testName}`;

  for (const { directory } of packages) {
    if (!fs.existsSync(path.join(repoRoot, directory, "vendor/bin/phpunit"))) {
      throw new Error(`${directory} has no vendor/bin/phpunit. Run \`composer install\` in ${directory} first.`);
    }
  }

  // Start from the snapshots committed now, so the bar has a total before PHPUnit has found its tests. Each run
  // corrects its package's share when PHPUnit reports its count, which also covers new and removed forms or DTOs.
  const committed = packages.map(({ directory, snapshots }) =>
    countSnapshots(path.join(repoRoot, directory, snapshots)),
  );
  let total = committed.reduce((sum, count) => sum + count, 0);

  progress.start(total, 0);

  const exitCodes = await Promise.all(
    packages.map(({ directory }, index) => {
      debug(chalk.blue(`Running ${command} in ${directory}...`));

      return runSnapshotTest(
        testName,
        path.join(repoRoot, directory),
        debug,
        (count) => {
          total += count - committed[index];
          progress.setTotal(total);
        },
        () => progress.increment(),
      ).catch(() => null);
    }),
  );

  progress.stop();

  const failed = packages.filter((_, index) => exitCodes[index] !== 0).map(({ directory }) => directory);

  if (failed.length > 0) {
    throw new Error(
      `Updating the ${testName} snapshots failed in ${listOf(failed)}. Run \`${command}\` there to see why.`,
    );
  }

  const changes = packages.flatMap(({ directory, snapshots }) =>
    exec(`git status --porcelain --untracked-files=all -- ${path.join(directory, snapshots)}`, debug, {
      cwd: repoRoot,
    })
      .stdout.split("\n")
      .filter(Boolean),
  );

  if (changes.length === 0) {
    console.log(chalk.green(`\n${testName}: no snapshot changed.\n`));
    return;
  }

  const count = (status: string) => changes.filter((line) => line.startsWith(status)).length;

  console.log(
    chalk.yellow(
      `\n${testName}: ${count(" M")} changed, ${count("??")} new, ${count(" D")} deleted. ` +
        "Read the diff before committing: a change meant to leave behaviour alone must not touch any snapshot.\n",
    ),
  );
  changes.forEach((line) => console.log(`  ${line}`));
  console.log();
}
