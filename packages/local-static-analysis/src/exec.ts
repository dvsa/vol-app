import shell from "shelljs";
import createDebug from "debug";

const exec = (
  command: string,
  debug: createDebug.Debugger = createDebug("static-analysis:*"),
  options: shell.ExecOptions & { async?: false | undefined } = {},
): shell.ShellString => {
  const optionsWithDefaults = {
    silent: true,
    env: {
      ...process.env,
      FORCE_COLOR: "1",
    },
    ...options,
  };

  const result = shell.exec(command, optionsWithDefaults);

  if (result.stdout && debug.enabled) {
    debug(result.stdout);
  }

  if (result.stderr) {
    debug(result.stderr);
  }

  if (result.code !== 0) {
    throw new Error(`Command: ${command} failed. Stderr: ${result.stderr}`);
  }

  return result;
};

export default exec;
