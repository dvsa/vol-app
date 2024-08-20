import chalk from "chalk";
import path from "node:path";
import { execSync } from "node:child_process";

const generateTerraformDocs = (filenames) => {
  try {
    execSync("terraform-docs --version", { stdio: "ignore" });
  } catch (error) {
    const message = `Warning: \`terraform-docs\` is not installed so cannot update Terraform module docs. Please install \`terraform-docs\`, see https://github.com/terraform-docs/terraform-docs/#installation.`;

    console.warn(chalk.yellow(message));
    return [];
  }

  const modules = [...new Set(filenames.map((filename) => path.dirname(filename)))];

  return [
    ...modules.map((module) => `terraform-docs markdown --output-file ${module}/README.md ${module}`),
    "git add infra/terraform/modules/**/README.md",
  ];
};

export default {
  "*": ["prettier --ignore-unknown --write"],
  "*.{tf,tfvars}": ["terraform fmt"],
  "infra/terraform/modules/**/*": generateTerraformDocs,
};
