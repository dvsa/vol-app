import prompts from "prompts";
import shell from "shelljs";
import chalk from "chalk";
import dedent from "dedent";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";

const debug = createDebug("refresh:actions:ResetLdap");

export default class ResetLdap implements ActionInterface {
  async prompt(): Promise<boolean> {
    const response = await prompts({
      type: "confirm",
      name: "ldap-refresh",
      message: "Do you want to reset the LDAP userpool?",
    });

    return response["ldap-refresh"];
  }

  async execute(): Promise<void> {
    debug(chalk.greenBright(`Deleting existing users`));

    const searchLdap = `ldapsearch -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -LLL -s one -b "ou=users,dc=vol,dc=dvsa" "(cn=*)" dn`;
    const search = shell.exec(
      `docker compose exec -T openldap /bin/bash -c '${searchLdap}' | awk '/^dn: / {print $2}'`,
      {
        silent: !debug.enabled,
      },
    );

    const allExistingUsers = search.stdout.split("\n").filter(Boolean);

    const deleteLdif = allExistingUsers.map((dn) => `dn: ${dn}\nchangetype: delete`).join("\n\n");

    const ldifDeletions = dedent`ldapmodify -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -c <<!
                                ${deleteLdif}
                                !`;

    const deleteUsers = shell.exec(`docker compose exec openldap /bin/bash -c "${ldifDeletions}"`);

    if (deleteUsers.code !== 0) {
      console.error(chalk.red(`Error while deleting users from LDAP`));
      return;
    }

    const selectAllUsersCmd = shell.exec(
      `docker compose exec db /bin/bash -c 'mysql -u mysql -polcs -N -e "SELECT login_id FROM olcs_be.user WHERE login_id IS NOT NULL"'`,
      { silent: !debug.enabled, env: { ...process.env, FORCE_COLOR: "1" } },
    );

    if (selectAllUsersCmd.code !== 0) {
      console.error(chalk.red(`Error while selecting users from database`));
      return;
    }

    const allUsers = selectAllUsersCmd.stdout.split("\n").filter(Boolean);

    const ldif = allUsers
      .map(
        (user) => dedent`dn: cn=${user},ou=users,dc=vol,dc=dvsa
      changetype: add
      objectClass: inetOrgPerson
      cn: ${user}
      sn: Bar
      userPassword: Password1`,
      )
      .join("\n\n");

    debug(chalk.greenBright(`Adding new users`));

    const ldifModify = dedent`ldapmodify -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -c <<!
                              ${ldif}
                              !`;

    if (
      shell.exec(`docker compose exec openldap /bin/bash -c "${ldifModify}"`, {
        env: {
          ...process.env,
          FORCE_COLOR: "1",
        },
      }).code !== 0
    ) {
      console.error(chalk.red(`Error while adding users to LDAP`));
      return;
    }
  }
}
