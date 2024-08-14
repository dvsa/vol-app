import prompts from "prompts";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import shell from "shelljs";

export default class FlushRedis implements ActionInterface {
  async prompt(): Promise<boolean> {
    const response = await prompts({
      type: "confirm",
      name: "should-flush",
      message: "Flush the Redis cache?",
      warn: "This will remove all cached data.",
    });

    return response["should-flush"];
  }

  async execute(): Promise<void> {
    if (shell.exec(`docker compose exec redis redis-cli -c "FLUSHALL"`, { silent: true }).code !== 0) {
      console.error(chalk.red(`Error: Failed to flush Redis cache`));
      return;
    }
  }
}
