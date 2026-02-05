<?php

declare(strict_types=1);

/**
 * Create VI extract files test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Domain\Command;

use Dvsa\Olcs\Cli\Domain\Command\CreateViExtractFiles;

/**
 * Create VI extract files test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateViExtractFilesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $params = [
            'op' => true,
            'oc' => true,
            'tnm' => true,
            'vhl' => true,
            'all' => true,
            'path' => '/tmp'
        ];
        $command = CreateViExtractFiles::create($params);

        foreach ($params as $key => $value) {
            if ($key !== 'path') {
                $this->assertTrue($command->{'get' . ucfirst($key)}());
            } else {
                $this->assertEquals('/tmp', $command->{'get' . ucfirst($key)}());
            }
        }
    }
}
