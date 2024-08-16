<?php

ini_set('memory_limit', -1);

// Config
{
    $users = [1, 2, 3, 4, 5, 6, 7, 8, 19, 20, 21, 22, 23, 24, 25, 12504, 12505];
    $entities = [1, 2, 6, 3, 7, 8];
    $iterations = 9000000;
    $table = 'application_read_audit';
    $field = 'application_id';
}

// Generation
{
    $output = 'TRUNCATE TABLE `' . $table . '`;';

    $rows = [];

    $keys = [];

    for ($i = 0; $i < $iterations; $i++) {

        $user = $users[array_rand($users)];
        $entity = $entities[array_rand($entities)];
        $date = date('Y-m-d', rand(strtotime('-50 year'), time()));

        if (isset($keys[$user . '-' . $entity . '-' . $date])) {

            $skip = true;

            foreach ($users as $user) {
                if (!isset($keys[$user . '-' . $entity . '-' . $date])) {
                    $skip = false;
                    break;
                }
            }

            if ($skip) {
                continue;
            }
        }

        $keys[$user . '-' . $entity . '-' . $date] = '1';

        $rows[] = sprintf(
            '(%s, %s, \'%s\')',
            $user,
            $application,
            $date
        );

        if ($i % 10000 == 0) {
            $output .= 'INSERT INTO `' . $table . '` (`user_id`, `' . $field . '`, `created_on`) VALUES '
                . implode(",\n", $rows) . ";\n";
            $rows = [];
        }
    }

    $output .= 'INSERT INTO `' . $table . '` (`user_id`, `' . $field . '`, `created_on`) VALUES '
        . implode(",\n", $rows) . ";\n";

    file_put_contents(__DIR__ . '/create_audit_test.sql', $output);
}
