<?php

namespace Dvsa\OlcsTest\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\Command\Surrender\Update;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new Update();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'communityLicenceDocumentStatus',
            'communityLicenceDocumentInfo',
            'digitalSignature',
            'discDestroyed',
            'discLost',
            'discLostInfo',
            'discStolen',
            'discStolenInfo',
            'licenceDocumentStatus',
            'licenceDocumentInfo',
            'status',
            'signatureType',
            'signatureChecked',
            'ecmsChecked',
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
            'communityLicenceDocumentStatus' => [
                'doc_sts_lost',
                'doc_sts_stolen',
                'doc_sts_destroyed',
            ],
            'digitalSignature' => ['1', '2'],
            'discDestroyed' => ['0', '1', '2'],
            'discLost' => ['0', '1', '2'],
            'discLostInfo' => ['text'],
            'discStolen' => ['0', '1', '2'],
            'discStolenInfo' => ['text'],
            'licenceDocumentStatus' => [
                'doc_sts_lost',
                'doc_sts_stolen',
                'doc_sts_destroyed',
            ],
            'licenceDocumentInfo' => [str_repeat('lice2', 100)],
            'communityLicenceDocumentInfo' => [str_repeat('abcde', 100)],
            'status' => [
                'surr_sts_approved',
                'surr_sts_comm_lic_docs_complete',
                'surr_sts_contacts_complete',
                'surr_sts_details_confirmed',
                'surr_sts_discs_complete',
                'surr_sts_lic_docs_complete',
                'surr_sts_signed',
                'surr_sts_start',
                'surr_sts_submitted',
                'surr_sts_withdrawn'
            ],
            'signatureType' => [
                'sig_physical_signature',
                'sig_digital_signature',
                'sig_signature_not_required'
            ],
            'signatureChecked' => [
                '1',
                '0'
            ],
            'ecmsChecked' => [
                '1',
                '0'
            ]
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', ['array']],
            'communityLicenceDocumentStatus' => [
                'invalid string',
                ['unexpected' => 'array'],
            ],
            'digitalSignature' => ['0', ['array']],
            'discDestroyed' => [['array']],
            'discLost' => [['array']],
            'discLostInfo' => [['unexpected' => 'array']],
            'discStolen' => [['array']],
            'discStolenInfo' => [['unexpected' => 'array']],
            'licenceDocumentInfo' => [str_repeat('hsgaa', 101)],
            'communityLicenceDocumentInfo' => [str_repeat('failu', 101)],
            'licenceDocumentStatus' => [
                'invalid string',
                ['unexpected' => 'array'],
            ],
            'status' => [
                'invalid string',
                ['unexpected' => 'array'],
            ],
            'signatureType' => [
                'rubbish'
            ],
            'signatureChecked' => [
                -99,
                2,
                ['array']
            ],
            'ecmsChecked' => [
                -99,
                2,
                ['array']
            ]
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [[99, '99'], ['string', '']],
            'communityLicenceDocumentStatus' => [['doc_sts_lost ', 'doc_sts_lost']],
            'digitalSignature' => [[99, '99'], ['string', '']],
            'discDestroyed' => [[99, '99']],
            'discLost' => [[99, '99']],
            'discLostInfo' => [['text ', 'text']],
            'discStolen' => [[99, '99']],
            'discStolenInfo' => [['text ', 'text']],
            'licenceDocumentStatus' => [['doc_sts_lost ', 'doc_sts_lost']],
            'status' => [['surr_sts_approved ', 'surr_sts_approved']],
            'signatureType' => [['sig_physical_signature ', 'sig_physical_signature']],
            'communityLicenceDocumentInfo' => [
                ['some Info ', 'some Info'],
            ],
            'licenceDocumentInfo' => [
                ['Some info ', 'Some info'],
            ],
            'signatureChecked' => [
                [1 , '1'],
                [0, '0'],
                ["1", "1"],
                ["0", "0"]
            ],
            'ecmsChecked' => [
                [1 , '1'],
                [0, '0'],
                ["1", "1"],
                ["0", "0"]
            ]
        ];
    }
}
