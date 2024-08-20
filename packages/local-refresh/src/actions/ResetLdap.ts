import prompts from "prompts";
import exec from "../exec";
import chalk from "chalk";
import dedent from "dedent";
import ActionInterface from "./ActionInterface";
import createDebug from "debug";
import { GenericBar } from "cli-progress";

const debug = createDebug("refresh:actions:ResetLdap");

export default class ResetLdap implements ActionInterface {
  async prompt(): Promise<boolean> {
    const { shouldRefresh } = await prompts({
      type: "confirm",
      name: "shouldRefresh",
      message: "Do you want to reset the LDAP userpool?",
    });

    return shouldRefresh;
  }

  async execute(progress: GenericBar): Promise<void> {
    progress.start(3, 0);

    debug(chalk.greenBright(`Deleting existing users from LDAP`));

    const searchLdap = `ldapsearch -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -LLL -s one -b "ou=users,dc=vol,dc=dvsa" "(cn=*)" dn`;
    const search = exec(
      `docker compose exec -T openldap /bin/bash -c '${searchLdap}' | awk '/^dn: / {print $2}'`,
      debug,
    );

    progress.increment();

    const allExistingUsers = search.stdout.split("\n").filter(Boolean);

    const deleteLdif = allExistingUsers.map((dn) => `dn: ${dn}\nchangetype: delete`).join("\n\n");

    const ldifDeletions = dedent`ldapmodify -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -c <<!
                                ${deleteLdif}
                                !`;

    const deleteUsers = exec(`docker compose exec openldap /bin/bash -c "${ldifDeletions}"`, debug);

    if (deleteUsers.code !== 0) {
      throw new Error("Delete users from LDAP failed");
    }

    progress.increment();

    const selectAllUsersCmd = exec(
      `docker compose exec db /bin/bash -c 'mysql -u mysql -polcs -N -e "SELECT login_id FROM olcs_be.user WHERE login_id IS NOT NULL"'`,
      debug,
    );

    if (selectAllUsersCmd.code !== 0) {
      throw new Error("Select users from database failed");
    }

    progress.increment();

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

    debug(`Adding new users into LDAP`);

    const ldifModify = dedent`ldapmodify -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -c <<!
                              ${ldif}
                              !`;
    exec(`docker compose exec openldap /bin/bash -c "${ldifModify}"`, debug);

    progress.stop();
  }
}
