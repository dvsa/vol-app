<?php
/**
 * Class UserPoolExport
 * Generates a CSV of user login_id and email addresses for cognito user pool recovery.
 *
 * @author Andy Newton <andy.newton@dvsa.gov.uk>
 */

class UserPoolExport
{
    /**
     * @var PDO
     */
    private $PDOConnection;

    /**
     * @var resource
     */
    private $outputStream;

    /**
     * @var DateTime
     */
    private $lastLogin;
    private string $separator;

    /**
     * UserPoolExport constructor.
     *
     * @param string $hostname
     * @param string $password
     * @param string $username
     * @param string $database
     * @param string $port
     * @param string $output
     * @param string $lastLogin
     */
    public function __construct(
        string $hostname,
        string $password,
        string $username,
        string $database,
        string $port,
        string $output,
        string $lastLogin,
        string $mode,
        string $separator
    )
    {
        $this->separator = $separator;

        $this->databaseConnections($hostname, $password, $username, $database, $port);

        if($mode === 'dr-export') {
            if (DateTime::createFromFormat('Y-m-d H:i:s', $lastLogin)) {
                $this->lastLogin = DateTime::createFromFormat('Y-m-d H:i:s', $lastLogin);
            } else {
                throw new RuntimeException("Date format invalid");
            }
        }

        $this->outputStream = ($output === 'stdout' || $output == "") ? fopen('php://stdout', 'w') : fopen($output, 'w');
    }

    /**
     * Establish connection to DB
     *
     * @param string $hostname
     * @param string $password
     * @param string $username
     * @param string $database
     * @param string $port
     */
    private function databaseConnections(string $hostname, string $password, string $username, string $database, string $port)
    {
        try {
            $this->PDOConnection = new PDO("mysql:host={$hostname};port={$port};dbname={$database}", $username, $password);
            $this->PDOConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Failure Message." . $e->getMessage();
            throw new RuntimeException("Error establishing database connections.");
        }
    }

    /**
     * perform query and export
     *
     * @return void
     */
    public function exportToCsv($append = null)
    {
        $query = $this->PDOConnection->prepare(
            "SELECT `user`.`login_id`,`contact_details`.`email_address`
                   FROM user
                   LEFT JOIN `contact_details` ON `user`.`contact_details_id` = `contact_details`.`id`
                   WHERE `user`.`last_login_at` > '{$this->lastLogin->format("Y-m-d H:i:s")}'
                   AND `user`.`account_disabled` = 0
                   AND `user`.`deleted_date` IS NULL");
        $query->execute();
        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach($users as $user) {
            fputcsv($this->outputStream, $user, $this->separator);
        }

        if ($append && file_exists($append)) {
            $src = fopen($append, 'r');
            stream_copy_to_stream($src, $this->outputStream);
        }
    }

    /**
     * perform query and export
     *
     * @return void
     */
    public function getUsersPerRole($usersPerRole = 2, $append = null)
    {
        // get :numUsers of users per role into an array.
        $query = $this->PDOConnection->prepare(
            "
            SELECT `user`.`login_id`, `role`.`role`, `contact_details`.`email_address` as `email_address`, `user`.`last_modified_on`
              FROM `user`
            JOIN `user_role` ON `user`.`id` = `user_role`.`user_id`
            JOIN `role` ON `user_role`.`role_id` = `role`.`id`
            LEFT JOIN `contact_details` ON `user`.`contact_details_id` = `contact_details`.`id`
            WHERE `user`.`id` IN (
              SELECT `user_id`
              FROM (
                SELECT `user_id`,
                @rownum := IF(@prev = `role_id`, @rownum + 1, 1) AS rownum,
                @prev := `role_id`
                FROM `user_role`
                JOIN (SELECT @prev := NULL, @rn := 0) AS vars
                ORDER BY `role_id` DESC
              ) AS T1
            WHERE rownum <= :usersPerRole
           )
           AND `user`.`deleted_date` IS NULL
           AND `user`.`account_disabled` = 0
           AND `email_address` IS NOT NULL");
        $query->execute([':usersPerRole' => $usersPerRole]);
        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        // Build concatenated login_id for CSV output only; do not modify the database.
        foreach($users as $key => $user) {
            if($user['last_modified_on'] != '2022-01-01 10:20:30.000000') {
                // concatenate but respect field length in case of unexpected length login ids.
                $concatLoginId = substr($user['login_id'].'.'.$user['role'],0,40);
                // update the array with login_id changes for csv output only.
                $users[$key]['login_id'] = $concatLoginId;
            }
            unset($users[$key]['role']);
            unset($users[$key]['last_modified_on']);
        }

        foreach($users as $user) {
            fputcsv($this->outputStream, $user, $this->separator);
        }

        if ($append && file_exists($append)) {
            $src = fopen($append, 'r');
            stream_copy_to_stream($src, $this->outputStream);
        }
    }
}