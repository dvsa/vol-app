import prompts from "prompts";
import fs from "node:fs";
import flatCache from "flat-cache";
import path from "node:path";
import exec from "../exec";
import chalk from "chalk";
import ActionInterface from "./ActionInterface";
import dedent from "dedent";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:ResetDatabase");

const cache = flatCache.load("reset-database");

enum DatabaseRefreshEnum {
  NONE,
  FULL,
  MIGRATIONS,
}

const liquibasePropertiesTemplate = dedent`
  url: jdbc:mysql://host.docker.internal/olcs_be?useSSL=false&allowPublicKeyRetrieval=true&characterEncoding=utf8&connectionCollation=utf8_general_ci
  username: root
  password: olcs
  changeLogFile: changesets/OLCS.xml
  logLevel: error
`;

export default class ResetDatabase implements ActionInterface {
  bucketName = "devapp-olcs-pri-olcs-deploy-s3";

  etlDirectory = "../olcs-etl";
  refreshType: DatabaseRefreshEnum = DatabaseRefreshEnum.NONE;

  liquibasePropertiesFileName = `vol-app.liquibase.properties`;
  createLiquibaseProperties = false;

  async prompt(): Promise<boolean> {
    const { shouldResetDatabase, refreshType } = await prompts([
      {
        type: "confirm",
        name: "shouldResetDatabase",
        message: "Reset the database?",
      },
      {
        type: (prev) => (prev === true ? "select" : null),
        name: "refreshType",
        message: "Choose the type of database reset?",
        choices: [
          { title: "Full refresh", value: DatabaseRefreshEnum.FULL },
          { title: "Just migrations", value: DatabaseRefreshEnum.MIGRATIONS },
        ],
      },
    ]);

    if (shouldResetDatabase === undefined || refreshType === undefined) {
      return false;
    }

    this.refreshType = refreshType;

    const { directory } = await prompts({
      type: "text",
      name: "directory",
      message: "Enter the path to the ETL directory",
      initial: cache.getKey("etlDirectory") || this.etlDirectory,
      validate: (value) =>
        fs.existsSync(path.isAbsolute(value) ? value : path.resolve(__dirname, "../../../../", value))
          ? true
          : "Path does not exist",
    });

    if (typeof directory !== "string") {
      return false;
    }

    this.etlDirectory = path.isAbsolute(directory) ? directory : path.resolve(__dirname, "../../../../", directory);

    cache.setKey("etlDirectory", this.etlDirectory);
    cache.save();

    debug(
      `Checking for liquibase properties file at: ${path.join(this.etlDirectory, this.liquibasePropertiesFileName)}`,
    );

    const liquibasePropertiesExists = fs.existsSync(path.join(this.etlDirectory, this.liquibasePropertiesFileName));

    const liquibasePropertiesIsDifferent =
      liquibasePropertiesExists &&
      fs.readFileSync(path.join(this.etlDirectory, this.liquibasePropertiesFileName), "utf8") !==
        liquibasePropertiesTemplate;

    if (!liquibasePropertiesExists || liquibasePropertiesIsDifferent) {
      const { createLiquibaseProperties } = await prompts({
        type: "confirm",
        name: "createLiquibaseProperties",
        message:
          liquibasePropertiesExists && liquibasePropertiesIsDifferent
            ? "Liquibase properties file is out-of-date. Overwrite?"
            : "Create liquibase properties file?",
      });

      if (!createLiquibaseProperties) {
        return false;
      }

      this.createLiquibaseProperties = createLiquibaseProperties;
    } else {
      debug("Liquibase properties file already exists and is up-to-date. Skipping step.");
    }

    return this.refreshType !== DatabaseRefreshEnum.NONE;
  }

  async execute(progress: GenericBar): Promise<void> {
    const isFullRefresh = this.refreshType === DatabaseRefreshEnum.FULL;

    progress.start(10, 0);

    if (isFullRefresh) {
      await this.#createBaseDatabase();
    }

    progress.increment(4);

    if (this.createLiquibaseProperties) {
      this.#createLiquidbasePropertiesFile();
    }

    progress.increment(1);

    await this.#runLiquibaseUpdate();

    progress.increment(5);

    if (isFullRefresh) {
      await this.#fetchAnonymisedDataset();
    }

    progress.stop();
  }

  async #createBaseDatabase(): Promise<void> {
    const myCnf = dedent`
    [client]
    user=root
    password=olcs
    host=host.docker.internal
    port=3306`;

    exec(
      dedent`docker compose exec db /bin/bash -c "\
        echo '${myCnf}' > ~/.my.cnf; \
        cd /var/lib/etl \
        && ./create-base.sh olcs_be
      "`,
      debug,
    );
  }

  #createLiquidbasePropertiesFile(): void {
    debug(`Creating liquibase properties file at: ${path.join(this.etlDirectory, this.liquibasePropertiesFileName)}`);

    fs.writeFileSync(path.join(this.etlDirectory, this.liquibasePropertiesFileName), liquibasePropertiesTemplate);
  }

  async #runLiquibaseUpdate(): Promise<void> {
    exec(
      `docker run \
      --rm \
      -e INSTALL_MYSQL=true \
      -v "${this.etlDirectory}":/liquibase/changelog \
      -w /liquibase/changelog \
      liquibase/liquibase \
        --defaultsFile=${this.liquibasePropertiesFileName} \
        update \
        -Ddataset=testdata
      `,
      debug,
    );
  }

  async #fetchAnonymisedDataset(): Promise<void> {
    // Full reset requires AWS credentials to pull down the anonymised dataset from S3.
    try {
      exec("aws sts get-caller-identity", debug);
    } catch (e: unknown) {
      throw new Error(
        "Valid AWS credentials are required for a full database reset. Authenticate with VOL `nonprod` and retry.",
      );
    }

    const latestAnonDatasetCmd = exec(
      `aws s3 ls s3://${this.bucketName}/anondata/olcs-db-localdev-anon-prod --recursive 2>/dev/null | sort | tail -n 1 | awk '{print $4}'`,
      debug,
    );

    if (latestAnonDatasetCmd.code !== 0) {
      throw new Error("Could not find the latest anonymised dataset on S3");
    }

    const latestAnonDataset = latestAnonDatasetCmd.stdout.trim();

    const cleanUp = () => {
      debug("Removing the anonymised dataset from the ETL directory");
      exec(`rm ${path.join(this.etlDirectory, "/olcs-db-localdev-anon-prod.sql.gz")}`, debug);
    };

    debug(chalk.greenBright(`Fetching the latest anonymised dataset from S3: ${latestAnonDataset}`));

    try {
      exec(
        `aws s3 cp s3://${this.bucketName}/${latestAnonDataset} ${path.join(this.etlDirectory, "/olcs-db-localdev-anon-prod.sql.gz")}`,
        debug,
      );

      exec(
        `docker compose exec -T db /bin/bash -c 'zcat /var/lib/etl/olcs-db-localdev-anon-prod.sql.gz | mysql -u mysql -polcs olcs_be'`,
        debug,
      );
    } finally {
      cleanUp();
    }
  }
}
