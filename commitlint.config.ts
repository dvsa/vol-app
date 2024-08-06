import type { UserConfig } from "@commitlint/types";
import * as fs from "fs";

const Configuration: UserConfig = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "header-max-length": [2, "always", 200],
    "scope-enum": [
      2,
      "always",
      [
        ...fs.readdirSync("app").filter((file) => fs.statSync(`app/${file}`).isDirectory()),
        ...fs.readdirSync("infra").filter((file) => fs.statSync(`infra/${file}`).isDirectory()),
      ],
    ],
  },
};

export default Configuration;
