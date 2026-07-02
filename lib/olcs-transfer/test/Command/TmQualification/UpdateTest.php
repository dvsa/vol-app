<?php

namespace Dvsa\OlcsTest\Transfer\Command\TmQualification;

use Dvsa\Olcs\Transfer\Command\TmQualification\Update;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new Update();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'id',
            'version',
            'serialNo',
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '123'],
            'version' => ['1', '2'],
            'qualificationType' => [
                "tm_qt_ar",
                "tm_qt_cpcsi",
                "tm_qt_cpcsn",
                "tm_qt_exsi",
                "tm_qt_exsn",
                "tm_qt_niar",
                "tm_qt_nicpcsi",
                "tm_qt_nicpcsn",
                "tm_qt_niexsi",
                "tm_qt_niexsn",
                'tm_qt_lgvar',
                'tm_qt_nilgvar',
            ],
            'serialNo' => [str_repeat('a', 50)],
            'countryCode' => ['a', 'aa'],
            'issuedDate' => ['2019-01-01']
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', ['array']],
            'version' => [['an_array']],
            'qualificationType' => ['sasdsd', ['an_array']],
            'serialNo' => [str_repeat('a', 51), ['an_array']],
            'countryCode' => [['aaa']],
            'issuedDate' => [['20-01-2001']]
        ];
    }


    protected function getFilterTransformations()
    {
        return [
            'id' => [[99, '99']],
            'version' => [[2, '2']],
            'qualificationType' => [['tm_qt_ar ', 'tm_qt_ar']],
            'serialNo' => ['aaaaa ', 'aaaaa'],
            'countryCode' => [['a ', 'a']],
        ];
    }
}
