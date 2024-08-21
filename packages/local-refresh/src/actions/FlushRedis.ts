import prompts from "prompts";
import ActionInterface from "./ActionInterface";
import exec from "../exec";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:FlushRedis");

export default class FlushRedis implements ActionInterface {
  async prompt(): Promise<boolean> {
    const { shouldFlush } = await prompts({
      type: "confirm",
      name: "shouldFlush",
      message: "Flush the Redis cache?",
      warn: "This will remove all cached data.",
    });

    return shouldFlush;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(1, 0);

    exec(`docker compose exec redis redis-cli -c "FLUSHALL"`, debug);

    progress.stop();
  }
}
