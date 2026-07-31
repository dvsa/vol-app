<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\Upload;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Document\Upload::class)]
final class UploadTest extends TestCase
{
    public function testStructure()
    {
        $filename = 'filename';
        $content = 'content';
        $irfoOrganisation = 1;
        $submission = 2;
        $trafficArea = 'B';
        $operatingCentre = 3;
        $opposition = 4;
        $category = 5;
        $subCategory = 6;
        $description = 'description';
        $isExternal = 1;
        $isScan = 1;
        $isEbsrPack = 1;
        $issuedDate = '01/01/2017';
        $user = 7;
        $shouldUploadOnly = true;
        $additionalCopy = true;
        $additionalEntities = ['application', 'licence'];
        $irhpApplication = 17;
        $surrender = 1;
        $isPostSubmissionUpload = 1;

        $data = [
            'filename' => $filename,
            'content' => $content,
            'irfoOrganisation' => $irfoOrganisation,
            'submission' => $submission,
            'trafficArea' => $trafficArea,
            'operatingCentre' => $operatingCentre,
            'opposition' => $opposition,
            'category' => $category,
            'subCategory' => $subCategory,
            'description' => $description,
            'isExternal' => $isExternal,
            'isScan' => $isScan,
            'isEbsrPack' => $isEbsrPack,
            'issuedDate' => $issuedDate,
            'user' => $user,
            'shouldUploadOnly' => $shouldUploadOnly,
            'additionalCopy' => $additionalCopy,
            'additionalEntities' => $additionalEntities,
            'irhpApplication' => $irhpApplication,
            'surrender' => $surrender,
            'isPostSubmissionUpload' => $isPostSubmissionUpload
        ];

        /** @var Upload $command */
        $command = Upload::create($data);

        $this->assertEquals($filename, $command->getFilename());
        $this->assertEquals($content, $command->getContent());
        $this->assertEquals($irfoOrganisation, $command->getIrfoOrganisation());
        $this->assertEquals($submission, $command->getSubmission());
        $this->assertEquals($trafficArea, $command->getTrafficArea());
        $this->assertEquals($operatingCentre, $command->getOperatingCentre());
        $this->assertEquals($opposition, $command->getOpposition());
        $this->assertEquals($category, $command->getCategory());
        $this->assertEquals($subCategory, $command->getSubCategory());
        $this->assertEquals($description, $command->getDescription());
        $this->assertEquals($isExternal, $command->getIsExternal());
        $this->assertEquals($isScan, $command->getIsScan());
        $this->assertEquals($isEbsrPack, $command->getIsEbsrPack());
        $this->assertEquals($issuedDate, $command->getIssuedDate());
        $this->assertEquals($user, $command->getUser());
        $this->assertEquals($shouldUploadOnly, $command->getShouldUploadOnly());
        $this->assertEquals($additionalCopy, $command->getAdditionalCopy());
        $this->assertEquals($additionalEntities, $command->getAdditionalEntities());
        $this->assertEquals($irhpApplication, $command->getIrhpApplication());
        $this->assertEquals($surrender, $command->getSurrender());
        $this->assertEquals($isPostSubmissionUpload, $command->getIsPostSubmissionUpload());
    }

    public function testSurrenderSetter()
    {
        $upload = new Upload();
        $upload->setSurrender(1);
        $this->assertEquals(1, $upload->getSurrender());
    }
}
