---
sidebar_position: 20
---

# Local Setup

## Before you start

:::tip

You can use the pre-configured devcontainers in the `.devcontainer` directory to get started quickly.

:::

You will need:

- [Git](https://git-scm.com/) `git --version`
- [Docker with Compose](https://docs.docker.com/manuals/) `docker -v`
- [Node.js ~22.16.0](https://nodejs.org/en/) `node -v`
- [PHP 8.2](https://www.php.net/) `php -v`
- [Composer](https://getcomposer.org/) `composer -V`
- [AWS CLI](https://aws.amazon.com/cli/) `aws --version`

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

:::tip

If you have [nvm](https://github.com/nvm-sh/nvm) installed, use `nvm use` to automatically switch to Node.js 22.16.0 as specified in `.nvmrc`.

:::

1. Install the NPM dependencies in the root directory

    ```bash
    npm install
    ```

1. Add DNS entries to your `/etc/hosts` file

    :::warning

    You only need to do this once.

    :::

    ```bash
    sudo echo "127.0.0.1 iuweb.local.olcs.dev-dvsacloud.uk ssweb.local.olcs.dev-dvsacloud.uk api.local.olcs.dev-dvsacloud.uk cdn.local.olcs.dev-dvsacloud.uk mailpit.local.olcs.dev-dvsacloud.uk" >> /etc/hosts
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

    :::info AWS Configuration Sync

    The refresh script includes an AWS Secrets and Parameters sync step that automatically updates your local PHP configuration files with values from AWS.

    For detailed information about configuring this sync, see [AWS Secrets and Parameters Sync](./aws-secrets-sync.md).

    :::

    :::info Download Remote Database

    The refresh script can download a copy of the DEV or INT database and restore it locally. This requires:

    - VPN connection to the DVSA network
    - AWS credentials for VOL nonprod
    - MySQL client tools on your host machine (not just Docker)

    **macOS setup:**

    ```bash
    brew install mysql-client
    echo 'export PATH="/opt/homebrew/opt/mysql-client/bin:$PATH"' >> ~/.zshrc
    ```

    The download is ~20GB and requires ~50GB free disk space.

    :::

:::success

All done!

You can visit the application in your browser:

- http://iuweb.local.olcs.dev-dvsacloud.uk
- http://ssweb.local.olcs.dev-dvsacloud.uk

Any email sent by the app can be viewed via: [Mailpit](http://mailpit.local.olcs.dev-dvsacloud.uk)

:::

## Logging in

The local dataset has a number of different users that you can log in as.

:::info

The default password for all users while using LDAP is `Password1`.

:::

### Selfserve

| Username   | Role                          |
| ---------- | ----------------------------- |
| `usr542`   | Operator - User               |
| `usr543`   | Operator - Transport Manager  |
| `usr611`   | Operator - Admin              |
| `usr612`   | Operator - Admin              |
| `usr778`   | Partner - Admin               |
| `usr779`   | Partner - Admin               |
| `usr1964`  | Partner - User                |
| `usr1965`  | Partner - User                |
| `usr20131` | Local Authority administrator |
| `usr20132` | Local Authority user          |

### Internal

| Username   | Role              |
| ---------- | ----------------- |
| `usr20`    | Case worker       |
| `usr21`    | Case worker       |
| `usr59`    | Admin             |
| `usr273`   | Admin             |
| `usr291`   | System Admin      |
| `usr322`   | Admin             |
| `usr331`   | Limited read only |
| `usr342`   | Limited read only |
| `usr455`   | Admin             |
| `usr528`   | Read only         |
| `usr529`   | Read only         |
| `usr1071`  | Admin             |
| `usr29431` | IRHP Admin        |
| `usr36047` | Admin             |
| `usr39158` | Admin             |
| `usr68648` | Admin             |
| `usr73852` | Admin             |
| `usr76189` | Admin             |
| `usr76754` | Admin             |

:::info

In the event that the local dataset changes, the above tables can be reproduced using the SQL:

```sql
SELECT u.login_id, r.description, IF(u.team_id IS NULL, "Selfserve", "Internal") as tenant FROM user u JOIN user_role ur ON u.id = ur.user_id JOIN role r ON r.id = ur.role_id;
```

:::
