<?php

/**
 * Document Naming Model test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Service\Document\NamingModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Document Naming Model test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class NamingModelTest extends MockeryTestCase
{
    /**
     * @var NamingService
     */
    protected $sut;

    public function setUpSut($date, $description, $extension, $category = null, $subCategory = null, $entity = null)
    {
        $this->sut = new NamingModel($date, $description, $extension, $category, $subCategory, $entity);
    }

    public function testGetDateWithU()
    {
        $now = new DateTime();
        $this->sut = new NamingModel($now, 'desc', 'ext');
        $date = $this->sut->getDate('d-M-Y h:i:s.u');
        $expected = $now->format('d-M-Y h:i:s.');

        $this->assertEquals($expected, substr($date, 0, 21));
        // can't actually test microseconds so let's test at least the length of returned date
        $this->assertEquals(27, strlen($date));
    }

    public function testGetDate()
    {
        $now = new DateTime();
        $this->sut = new NamingModel($now, 'desc', 'ext');
        $date = $this->sut->getDate('d-M-Y');
        $expected = $now->format('d-M-Y');

        $this->assertEquals($expected, $date);
    }

    public function testGetCategory()
    {
        $category = new Category();
        $category->setDescription('catdesc');

        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext', $category);

        $this->assertEquals('catdesc', $this->sut->getCategory());
    }

    public function testGetCategoryEmpty()
    {
        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext');
        $this->assertNull($this->sut->getCategory());
    }

    public function testGetSubCategory()
    {
        $subCategory = new SubCategory();
        $subCategory->setSubCategoryName('subcatname');

        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext', null, $subCategory);

        $this->assertEquals('subcatname', $this->sut->getSubCategory());
    }

    public function testGetSubCategoryEmpty()
    {
        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext');
        $this->assertNull($this->sut->getSubCategory());
    }

    public function testDescription()
    {
        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext');
        $this->assertEquals('desc', $this->sut->getDescription());
    }

    public function testExtension()
    {
        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext');
        $this->assertEquals('ext', $this->sut->getExtension());
    }

    public function testGetContext()
    {
        $organisation = new Organisation();
        $organisation->setId(77);

        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext', null, null, $organisation);

        $this->assertEquals(77, $this->sut->getContext());
    }

    public function testGetContextEmpty()
    {
        $this->sut = new NamingModel(new DateTime(), 'desc', 'ext');
        $this->assertEquals('', $this->sut->getContext());
    }
}
