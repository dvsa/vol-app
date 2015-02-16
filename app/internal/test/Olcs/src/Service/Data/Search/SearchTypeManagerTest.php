<?php

namespace OlcsTest\Service\Data\Search;

use Olcs\Data\Object\Search\Licence;
use Olcs\Service\Data\Search\SearchTypeManager;

/**
 * Class SearchTypeManagerTest
 * @package OlcsTest\Service\Data\Search
 */
class SearchTypeManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $valid = new Licence();
        $invalid = new \stdClass();

        $sut = new SearchTypeManager();

        $sut->validatePlugin($valid);

        $passed = false;

        try {
            $sut->validatePlugin($invalid);
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Invalid class') {
                $passed = true;
            }
        }

        $this->assertTrue($passed, 'Expected exception no thrown or message didn\'t match');
    }
}
