<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrhpStartDate;

/**
 * Class IrhpStartDate
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class IrhpStartDateTest extends SingleValueTestAbstract
{
    public const SUT_CLASS_NAME = IrhpStartDate::class;

    #[\Override]
    public function testRender(): void
    {
        $sutClassName = static::SUT_CLASS_NAME;

        $bookmark = new $sutClassName();

        $key = $sutClassName::FIELD;
        $value = date("Y-m-d H:i:s", strtotime('tomorrow midnight'));

        $bookmark->setData([$key => $value]);

        $formatter = parent::getFormatter();
        $class = $formatter::class;
        $valueExpected = $class::format((array)$value);

        $this->assertEquals($valueExpected, $bookmark->render());
    }

    public function testRenderNow(): void
    {
        $sutClassName = static::SUT_CLASS_NAME;

        $bookmark = new $sutClassName();

        $key = $sutClassName::FIELD;
        $value = date('Y-m-d H:i:s', strtotime('yesterday midnight'));

        $bookmark->setData([$key => $value]);

        $formatter = parent::getFormatter();
        $class = $formatter::class;
        $valueToday = date('Y-m-d H:i:s', strtotime('today midnight'));
        $valueExpected = $class::format((array)$valueToday);

        $this->assertEquals($valueExpected, $bookmark->render());
    }
}
