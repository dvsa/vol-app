import prompts from "prompts";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";
import updateSnapshots, { confirmInstalledVersions, SnapshotPackage } from "../utils/SnapshotUpdater";

const debug = createDebug("refresh:actions:UpdateFormSnapshots");

// Every package, not just the one you changed: an olcs-common fieldset is composed into app forms
const packages: SnapshotPackage[] = [
  { directory: "lib/olcs-auth", snapshots: "test/Form/Snapshot/forms" },
  { directory: "lib/olcs-common", snapshots: "test/Common/src/Common/Form/Snapshot/forms" },
  { directory: "app/internal", snapshots: "test/Olcs/src/Form/Snapshot/forms" },
  { directory: "app/selfserve", snapshots: "test/Olcs/src/Form/Snapshot/forms" },
];

export default class UpdateFormSnapshots implements ActionInterface {
  async prompt(): Promise<boolean> {
    const { shouldUpdate } = await prompts({
      type: "confirm",
      name: "shouldUpdate",
      message: "Update the form test snapshots (olcs-auth, olcs-common, internal, selfserve)?",
      initial: false,
    });

    return shouldUpdate && (await confirmInstalledVersions(packages));
  }

  async execute(progress: GenericBar): Promise<void> {
    await updateSnapshots("FormSpecificationSnapshotTest", packages, progress, debug);
  }
}
