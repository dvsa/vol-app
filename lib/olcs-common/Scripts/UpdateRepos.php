<?php

$directory = __DIR__ . '/../../';

if ($handle = opendir($directory)) {

    while (false !== ($entry = readdir($handle))) {

        if (preg_match('/olcs\-[a-zA-Z\-]+/', $entry)) {

            echo "\n" . $entry . " Repo\n";
            $branches = shell_exec('cd ' . $directory . '/' . $entry . ' && git branch');

            preg_match('/\* ([^\ ]+)/', $branches, $matches);

            $oldBranch = $matches[1];

            $branches = explode("\n", $branches);

            foreach ($branches as $key => $branch) {

                $branch = trim(str_replace('*', '', $branch));

                if ($branch === '' || $branch === '0') {

                    unset($branches[$key]);

                } else {

                    $branches[$key] = $branch;
                }
            }

            echo "\n" . 'git fetch -p origin' . "\n";
            echo shell_exec('cd ' . $directory . '/' . $entry . ' && git fetch -p origin');

            $status = shell_exec('cd ' . $directory . '/' . $entry . ' && git status');

            // If we have nothing to commit
            if (strstr($status, 'nothing to commit')) {

                // If the branch is not up to date
                if (strstr($status, 'Your branch is up-to-date') === '' || strstr($status, 'Your branch is up-to-date') === '0' || !str_contains($status, 'Your branch is up-to-date')) {

                    echo "\n" . 'Update the current branch' . "\n";
                    echo shell_exec('cd ' . $directory . '/' . $entry . ' && git pull origin ' . $oldBranch);
                    echo shell_exec('cd ' . $directory . '/' . $entry . ' && composer update');

                } else {

                    echo "\n" . 'Your branch is up to date';
                }
            }
        }
    }
}
