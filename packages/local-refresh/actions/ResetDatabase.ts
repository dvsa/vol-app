import prompts from "prompts";
import fs from "node:fs";
import flatCache from "flat-cache";
import shell from "shelljs";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import dedent from "dedent";
import createDebug from "debug";

const debug = createDebug("refresh:actions:ResetDatabase");

const cache = flatCache.load("reset-database");

enum DatabaseRefreshEnum {
  FULL,
  MIGRATIONS,
  NONE,
}

export default class ResetDatabase implements ActionInterface {
  bucketName = "devapp-olcs-pri-olcs-deploy-s3";

  etlDirectory: string | undefined;
  refreshType: DatabaseRefreshEnum = DatabaseRefreshEnum.NONE;

  async prompt(): Promise<boolean> {
    const response = await prompts([
      {
        type: "confirm",
        name: "database-refresh",
        message: "Reset the database?",
      },
      {
        type: (prev) => (prev === true ? "select" : null),
        name: "refresh-type",
        message: "Choose the type of database reset?",
        choices: [
          { title: "Full refresh", value: DatabaseRefreshEnum.FULL },
          { title: "Just migrations", value: DatabaseRefreshEnum.MIGRATIONS },
        ],
      },
    ]);

    if (response["refresh-type"] === undefined) {
      return false;
    }

    this.refreshType = response["refresh-type"];

    const etlDirectoryPrompt = await prompts({
      type: "text",
      name: "directory",
      message: "Enter the path to the ETL directory",
      initial: cache.getKey("etlDirectory") || "../olcs-etl",
      validate: (value) => (fs.existsSync(value) ? true : "Path does not exist"),
    });

    if (etlDirectoryPrompt.directory === undefined) {
      return false;
    }

    cache.setKey("etlDirectory", etlDirectoryPrompt.directory);
    cache.save();

    this.etlDirectory = etlDirectoryPrompt.directory;

    return this.refreshType !== DatabaseRefreshEnum.NONE;
  }

  async execute(): Promise<void> {
    // Full reset requires AWS credentials to pull down the anonymised dataset from S3.
    if (this.refreshType === DatabaseRefreshEnum.FULL) {
      if (shell.exec("aws sts get-caller-identity").code !== 0) {
        console.error(
          chalk.red(
            "Error: Valid AWS credentials are required for a full database reset. Authenticate with VOL `nonprod` and retry.",
          ),
        );

        return;
      }
    }

    const myCnf = dedent`
    [client]
    user=root
    password=olcs
    host=host.docker.internal
    port=3306`;

    if (this.refreshType !== DatabaseRefreshEnum.MIGRATIONS) {
      if (
        shell.exec(
          dedent`docker compose exec db /bin/bash -c "\
          echo '${myCnf}' > ~/.my.cnf; \
          cd /var/lib/etl \
          && ./create-base.sh olcs_be
        "
      `,
          {
            env: {
              ...process.env,
              FORCE_COLOR: "1",
            },
          },
        ).code !== 0
      ) {
        console.error(chalk.red(`Error: \`create-base.sh\` failed`));
        return;
      }
    }

    if (
      shell.exec(
        `docker run \
        --rm \
        -e INSTALL_MYSQL=true \
        -v "$PWD/${this.etlDirectory}/":/liquibase/changelog \
        -w /liquibase/changelog \
        liquibase/liquibase \
          --defaultsFile=liquibase.properties \
          update \
          -Ddataset=testdata`,
        {
          env: {
            ...process.env,
            FORCE_COLOR: "1",
          },
        },
      ).code !== 0
    ) {
      console.error(chalk.red(`Error: \`liquibase\` failed`));
      return;
    }

    // Fetch file from S3.
    const latestAnonDatasetCmd = shell.exec(
      `aws s3 ls s3://${this.bucketName}/anondata/olcs-db-localdev-anon-prod --recursive 2>/dev/null | sort | tail -n 1 | awk '{print $4}'`,
      {
        silent: !debug.enabled,
      },
    );

    if (latestAnonDatasetCmd.code !== 0) {
      console.error(chalk.red("Error: Could not find the latest anonymised dataset on S3"));
      return;
    }

    const latestAnonDataset = latestAnonDatasetCmd.stdout.trim();

    debug(chalk.greenBright(`Fetching the latest anonymised dataset from S3: ${latestAnonDataset}`));

    if (
      shell.exec(
        `aws s3 cp s3://${this.bucketName}/${latestAnonDataset} ${this.etlDirectory}/olcs-db-localdev-anon-prod.sql.gz`,
      ).code !== 0
    ) {
      console.error(chalk.red("Error: Could not fetch the latest anonymised dataset from S3"));

      shell.exec(`rm ${this.etlDirectory}/olcs-db-localdev-anon-prod.sql.gz`);
      return;
    }

    if (
      shell.exec(
        `docker compose exec -T db /bin/bash -c 'zcat /var/lib/etl/olcs-db-localdev-anon-prod.sql.gz | mysql -u mysql -polcs olcs_be'`,
        {
          silent: !debug.enabled,
        },
      ).code !== 0
    ) {
      console.error(chalk.red("Error: Could not import the anonymised dataset into the database"));

      shell.exec(`rm ${this.etlDirectory}/olcs-db-localdev-anon-prod.sql.gz`);
      return;
    }

    shell.exec(`rm ${this.etlDirectory}/olcs-db-localdev-anon-prod.sql.gz`);
  }
}
