<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Toggle;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Qandidate\Toggle\Serializer\InMemoryCollectionSerializer;
use Qandidate\Toggle\ToggleManager;

/**
 * Class ToggleServiceTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ToggleServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testEnabledAndDisabled(): void
    {
        $testFeatures = [
            'feature1' => [
                'name' => 'toggle1',
                'conditions' => [],
                'status' => 'inactive',
            ],
            'feature2' => [
                'name' => 'toggle2',
                'conditions' => [],
                'status' => 'always-active',
            ],
        ];

        $collectionSerializer = new InMemoryCollectionSerializer();
        $collection = $collectionSerializer->deserialize($testFeatures);

        $sut = new ToggleService(
            new ToggleManager($collection)
        );

        self::assertEquals(false, $sut->isEnabled('toggle1'));
        self::assertEquals(true, $sut->isEnabled('toggle2'));

        $sut->enable('toggle1');
        $sut->disable('toggle2');

        self::assertEquals(true, $sut->isEnabled('toggle1'));
        self::assertEquals(false, $sut->isEnabled('toggle2'));
    }
}
