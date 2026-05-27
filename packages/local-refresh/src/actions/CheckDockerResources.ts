import prompts from "prompts";
import chalk from "chalk";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import ActionInterface from "./ActionInterface";
import exec from "../exec";

const debug = createDebug("refresh:actions:CheckDockerResources");

// 12 GB is the working floor (4× PHP opcache @ 512 MB ≈ 2 GB, plus MySQL,
// LibreOffice, Redis, page cache for bind-mount IO, VM overhead).
// 16 GB gives slack for parallel work (running tests while the stack is up,
// docker builds, IDE indexing). Above ~20 GB the gains taper off.
const MIN_MEMORY_GB = 12;
const RECOMMENDED_MEMORY_GB = 16;
const MIN_CPUS = 4;

interface DockerSystemInfo {
  MemTotal?: number;
  NCPU?: number;
}

export default class CheckDockerResources implements ActionInterface {
  async prompt(): Promise<boolean> {
    debug("Querying Docker for resource limits");

    let stdout: string;
    try {
      stdout = exec("docker system info --format '{{json .}}'", debug).stdout;
    } catch {
      console.log(
        chalk.yellow("\nCould not query Docker — is Docker Desktop / OrbStack running? Skipping resource check.\n"),
      );
      return false;
    }

    let info: DockerSystemInfo;
    try {
      info = JSON.parse(stdout);
    } catch {
      console.log(chalk.yellow("\nDocker returned unexpected output; skipping resource check.\n"));
      return false;
    }

    const memoryGb = (info.MemTotal ?? 0) / 1024 ** 3;
    const cpus = info.NCPU ?? 0;
    const memoryOk = memoryGb >= MIN_MEMORY_GB;
    const cpusOk = cpus >= MIN_CPUS;

    if (memoryOk && cpusOk) {
      console.log(
        chalk.green(`Docker resources OK — ${memoryGb.toFixed(1)} GB RAM, ${cpus} CPUs available to containers.`),
      );
      return false;
    }

    console.log(chalk.bold("\nDocker resource check:"));
    if (!memoryOk) {
      console.log(
        chalk.yellow(
          `  Memory: ${memoryGb.toFixed(1)} GB allocated — VOL recommends ≥ ${MIN_MEMORY_GB} GB (${RECOMMENDED_MEMORY_GB} GB comfortable).`,
        ),
      );
    }
    if (!cpusOk) {
      console.log(chalk.yellow(`  CPUs:   ${cpus} allocated — VOL recommends ≥ ${MIN_CPUS}.`));
    }
    console.log(
      chalk.dim(
        "\n  Docker Desktop → Settings → Resources to raise the limits.\n  OrbStack typically auto-scales and does not need configuration.\n",
      ),
    );

    const { proceed } = await prompts({
      type: "confirm",
      name: "proceed",
      message: "Continue with refresh anyway?",
      initial: true,
    });

    if (!proceed) {
      console.log(chalk.cyan("\nAborted by user. Raise the Docker resource limits and re-run.\n"));
      process.exit(0);
    }

    return false;
  }

  async execute(_progress: GenericBar): Promise<void> {
    // Check runs in prompt(); execute is a no-op so this fits the action pattern cleanly.
  }
}
