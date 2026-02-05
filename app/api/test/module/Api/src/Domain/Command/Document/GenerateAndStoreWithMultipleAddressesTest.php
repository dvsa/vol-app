<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStoreWithMultipleAddresses;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GenerateAndStoreWithMultipleAddressesTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $command = GenerateAndStoreWithMultipleAddresses::create(
            [
                'GenerateCommandData' => [],
                'addressBookmark' => [],
                'sendToAddresses' => [],
                'bookmarkBundle' => []
            ]
        );

        $this->assertEquals([], $command->getGenerateCommandData());
        $this->assertEquals([], $command->getAddressBookmark());
        $this->assertEquals([], $command->getSendToAddresses());
        $this->assertEquals([], $command->getBookmarkBundle());
    }
}
