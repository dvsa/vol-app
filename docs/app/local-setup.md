---
sidebar_position: 20
---

# Local Setup

## Before you start

:::tip

You can use the pre-configured devcontainers in the `.devcontainer` directory to get started quickly.

:::

You will need:

-   [Git](https://git-scm.com/) `git --version`
-   [Docker with Compose](https://docs.docker.com/manuals/) `docker -v`
-   [Node.js 20](https://nodejs.org/en/) `node -v`
-   [PHP 8.2](https://www.php.net/) `php -v`
-   [Composer](https://getcomposer.org/) `composer -V`
-   [AWS CLI](https://aws.amazon.com/cli/) `aws --version`

## Getting started

1. Clone the repository

    ```bash
    git clone git@github.com:dvsa/vol-app.git
    ```

1. Change into the project directory

    ```bash
    cd vol-app
    ```

    :::warning

    If you are resetting the database you will need to be be authenticated with the AWS VOL `nonprod` account.

    If you have the ZSH AWS plugin installed & configured, you can run the following command to get temporary credentials:

    ```bash
    acp [profile]
    ```

    :::

## Running the app

1. Install the NPM dependencies in the root directory

    ```bash
    npm install
    ```

1. Add DNS entries to your `/etc/hosts` file

    :::warning

    You only need to do this once.

    :::

    ```bash
    sudo echo "127.0.0.1 iuweb.local.olcs.dev-dvsacloud.uk ssweb.local.olcs.dev-dvsacloud.uk api.local.olcs.dev-dvsacloud.uk cdn.local.olcs.dev-dvsacloud.uk" >> /etc/hosts
    ```

1. Start the application

    :::warning

    The `olcs-etl` project needs to be mounted as a volume in the `db` container. By default, the directory is set to `../olcs-etl`.

    You can customise this using the `OLCS_ETL_DIR` environment variable before running the `docker compose` command.

    ```sh
    export OLCS_ETL_DIR=/path/to/olcs-etl
    ```

    :::

    ```bash
    docker compose up -d
    ```

1. Run the local setup script - this script will also as a local reset and is safe to run multiple times.

    :::info

    If this is the first time you are running the application, you will need to run all the steps.

    :::

    ```bash
    npm run refresh
    ```

:::success

All done!

Visit the application in your browser: - [Internal Application](http://iuweb.local.olcs.dev-dvsacloud.uk) - [Self Service Application](http://ssweb.local.olcs.dev-dvsacloud.uk)

:::
