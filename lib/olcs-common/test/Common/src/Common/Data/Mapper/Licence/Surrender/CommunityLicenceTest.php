<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CommunityLicence;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class CommunityLicenceTest extends MockeryTestCase
{
    private $communityLicence;

    #[\Override]
    protected function setUp(): void
    {
        $this->communityLicence = new CommunityLicence();
    }

    /**
     *
     * @param $apiData
     * @param $formData
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultData')]
    public function testMapFromResult($apiData, $formData): void
    {
        $this->assertEquals($formData, $this->communityLicence->mapFromResult($apiData));
    }

    /**
     * @param $apiData
     * @param $formData
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('resultData')]
    public function testMapFromForm($apiData, $formData): void
    {
        $apiData['communityLicenceDocumentStatus'] = $apiData['communityLicenceDocumentStatus']['id'];

        $this->assertEquals($apiData, $this->communityLicence->mapFromForm($formData));
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(array<string> | string)> | string | null)>>>
     *
     * @psalm-return array{possession: array{apiData: array{communityLicenceDocumentStatus: array{id: 'doc_sts_destroyed'}, communityLicenceDocumentInfo: null}, formData: array{communityLicenceDocument: array{communityLicenceDocument: 'possession'}}}, lost: array{apiData: array{communityLicenceDocumentStatus: array{id: 'doc_sts_lost'}, communityLicenceDocumentInfo: 'lost info'}, formData: array{communityLicenceDocument: array{communityLicenceDocument: 'lost', lostContent: array{details: 'lost info'}}}}, stolen: array{apiData: array{communityLicenceDocumentStatus: array{id: 'doc_sts_stolen'}, communityLicenceDocumentInfo: 'stolen info'}, formData: array{communityLicenceDocument: array{communityLicenceDocument: 'stolen', stolenContent: array{details: 'stolen info'}}}}}
     */
    public static function resultData(): \Iterator
    {
        yield 'possession' => [
            'apiData' => [

                "communityLicenceDocumentStatus" => ['id' => 'doc_sts_destroyed'],
                "communityLicenceDocumentInfo" => null,

            ],
            'formData' => [
                'communityLicenceDocument' => [
                    'communityLicenceDocument' => 'possession'
                ]
            ]
        ];
        yield 'lost' => [
            'apiData' => [

                "communityLicenceDocumentStatus" => ['id' => 'doc_sts_lost'],
                "communityLicenceDocumentInfo" => 'lost info',

            ],
            'formData' => [
                'communityLicenceDocument' => [
                    'communityLicenceDocument' => 'lost',
                    'lostContent' => [
                        'details' => 'lost info'
                    ]
                ]
            ]
        ];
        yield 'stolen' => [
            'apiData' => [

                "communityLicenceDocumentStatus" => ['id' => 'doc_sts_stolen'],
                "communityLicenceDocumentInfo" => 'stolen info',

            ],
            'formData' => [
                'communityLicenceDocument' => [
                    'communityLicenceDocument' => 'stolen',
                    'stolenContent' => [
                        'details' => 'stolen info'
                    ]
                ]
            ]
        ];
    }

    public function getStatusForId(string $id): string
    {
        return match ($id) {
            'doc_sts_destroyed' => 'possession',
            'doc_sts_lost' => 'lost',
            'doc_sts_stolen' => 'stolen',
            default => '',
        };
    }
}
