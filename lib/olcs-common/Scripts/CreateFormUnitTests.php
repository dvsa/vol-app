<?php
// @codingStandardsIgnoreFile

/**
 * Class to create form unit test files
 */
class CreateFromUnitTests
{
    public const UT_TEMPLATE_FILE_NAME = 'FormUnitTestTemplate.php';

    private $path;

    /**
     * Set path to dir with forms
     *
     * @param string $path Path to dir with forms
     *
     * @return $this
     */
    public function setSourcePath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Create missing form unit tests
     */
    public function exec(): void
    {
        // load the template
        $template = file_get_contents(__DIR__ . '/'. self::UT_TEMPLATE_FILE_NAME);

        // get list of existing forms
        $pathToForms = realpath(__DIR__ . '/'. $this->path);
        $formsList = $this->getDirContents($pathToForms);

        foreach($formsList as $formPath) {
            print sprintf('Form: %s%s', $formPath, PHP_EOL);

            // set test path
            $testPath = strtr(
                $formPath,
                [
                    '/module/' => '/test/',
                    '/Common/src/Common/' => '/test/Common/src/Common/',
                    '.php' => 'Test.php',
                ]
            );
            print sprintf('Test: %s%s', $testPath, PHP_EOL);

            // set test dir path
            $testDirPath = dirname($testPath);

            if (!is_dir($testDirPath)) {
                // create the folder
                mkdir($testDirPath, 0777, true);
            }

            if (!file_exists($testPath)) {
                // create the test
                $testParts = [];
                preg_match('/.+\/test\/([^\/]+)\/src\/(.+)/', $testDirPath, $testParts);
                $testNameSpace = str_replace(
                    ['/', 'CommonTest\Common'],
                    ['\\', 'CommonTest'],
                    $testParts[1].'Test/'.$testParts[2]
                );
                $formClassName = str_replace(
                    ['/', 'Common\Common'],
                    ['\\', 'Common'],
                    '/'.$testParts[1].'/'.$testParts[2].'/'.basename($formPath, '.php')
                );

                file_put_contents(
                    $testPath,
                    strtr(
                        $template,
                        [
                            'R_NAMESPACE' => $testNameSpace,
                            'R_TEST_CLASS_NAME' => basename($testPath, '.php'),
                            'R_FORM_CLASS_NAME' => $formClassName,
                        ]
                    )
                );
                print "Created\n";
            } else {
                print "Already exists\n";
            }

            print "\n";
        }
    }

    /**
     * Get dir contents
     *
     * @param string $dir     Path to dir
     * @param array  $results Results
     *
     * @return array
     */
    private function getDirContents($dir, &$results = [])
    {
        $files = scandir($dir);

        foreach ($files as $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);

            if(!is_dir($path)) {
                $results[] = $path;
            } elseif($value !== "." && $value !== "..") {
                $this->getDirContents($path, $results);
            }
        }

        return $results;
    }
}

/*
 * Call php CreateFormUnitTests.php <path-to-dir-with-forms>
 */
if (empty($argv[1])) {
    print "usage: php CreateFormUnitTests.php <path-to-dir-with-forms>\n";
    exit;
}

(new CreateFromUnitTests)
    ->setSourcePath($argv[1])
    ->exec();
