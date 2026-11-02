import prompts from "prompts";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import updateSnapshots, { confirmInstalledVersions, SnapshotPackage } from "../utils/SnapshotUpdater";

const debug = createDebug("refresh:actions:UpdateDtoSnapshots");

const packages: SnapshotPackage[] = [{ directory: "lib/olcs-transfer", snapshots: "test/Snapshot/dto" }];

export default class UpdateDtoSnapshots implements ActionInterface {
  async prompt(): Promise<boolean> {
    const { shouldUpdate } = await prompts({
      type: "confirm",
      name: "shouldUpdate",
      message: "Update the DTO test snapshots (olcs-transfer)?",
      initial: false,
    });

    return shouldUpdate && (await confirmInstalledVersions(packages));
  }

  async execute(progress: GenericBar): Promise<void> {
    await updateSnapshots("DtoSnapshotTest", packages, progress, debug);
  }
}
