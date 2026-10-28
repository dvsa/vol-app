<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\LocalAuthorityResolver;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use Dvsa\Olcs\Api\Service\Ebsr\Mapping\TransExchangeXmlFactory;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\OlcsTest\Api\Service\Ebsr\LocalAuthorityDirectory;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorPluginManager;
use Olcs\XmlTools\Module as XmlToolsModule;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Runs every example DfT ships with the TransXChange 2.5.2 schema through the real EBSR xml structure and bus
 * registration inputs, built from the real config. The examples directory is read in place and never modified.
 *
 * Not every example is a registration document: some are timetable-only (RegistrationDocument false or missing),
 * deltas, WGS84 or an older namespace, and the schema rejects those. Some registration documents leave out
 * ServiceClassification, which VOL requires. Each is listed in EXPECTED_REJECTIONS with the reason it is rejected;
 * every other example must be accepted.
 */
final class TransXChangeExamplesTest extends TestCase
{
    private const string EXAMPLES_DIR = '/module/Api/data/ebsr/xsd/TransXChange_schema_2.5.2/examples';

    private const string TXC_NAMESPACE = 'http://www.transxchange.org.uk/';

    private const string NOT_REGISTRATION_DOCUMENT =
        "attribute 'RegistrationDocument': The value 'false' does not match the fixed value constraint 'true'";

    private const string NO_REGISTRATION_DOCUMENT_ATTRIBUTE =
        "The attribute 'RegistrationDocument' is required but missing";

    private const string WGS84 = "attribute 'LocationSystem': The value 'WGS84' does not match the fixed value";

    private const string NO_SERVICE_CLASSIFICATION = 'Service classification element is missing from the XML file';

    private const array EXPECTED_REJECTIONS = [
        'cancellation/cancellation.xml' => self::NO_SERVICE_CLASSIFICATION,
        'circular/circular.xml' => self::WGS84,
        'circular/circular_reordered.xml' => self::WGS84,
        'cloverleaf/cloverleaf.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'delta/delta.xml' => "TransXChangeDeltas': No matching global declaration available for the validation root",
        'express/express.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'express/express_stopalloc.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'express/expressLcl.xml' => self::NO_REGISTRATION_DOCUMENT_ATTRIBUTE,
        'interchange/73c6caeb71634145a88d80be9d407c87/interchange.xml' => self::NO_SERVICE_CLASSIFICATION,
        'interchange/eeb789126b744c55b3d2d9ceb585f6d6/interchange.xml' => self::NO_SERVICE_CLASSIFICATION,
        'interchange/interchange.xml' => self::NO_SERVICE_CLASSIFICATION,
        'interchange/interchange_multi.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'linear/linear_change.xml' =>
            'Missing child element(s). Expected is ( {http://www.transxchange.org.uk/}Registrations )',
        'linear/linear_change_jp.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'linear/linear_change_vj.xml' => self::NO_REGISTRATION_DOCUMENT_ATTRIBUTE,
        'linear/linearx.xml' =>
            "Element '{http://www.transxchange.org.uk/txc}TransXChange': No matching global declaration available",
        'lollipop/f98668b3ba4240b8923702b5199da334/lollipop.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'lollipop/lollipop.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'lollipop/lollipop2xml.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'lollipop/plainlollipop.xml' => self::NOT_REGISTRATION_DOCUMENT,
        'operators/operators.xml' => self::NO_REGISTRATION_DOCUMENT_ATTRIBUTE,
    ];

    private ServiceManager $container;

    #[\Override]
    protected function setUp(): void
    {
        $config = ArrayUtils::merge(
            require self::apiDir() . '/module/Api/config/module.config.php',
            (new XmlToolsModule())->getConfig()
        );
        $config['ebsr'] = (require self::apiDir() . '/config/autoload/global.php')['ebsr'];
        $config['xml_valid_message_exclude'] =
            (require self::apiDir() . '/config/autoload/config.global.php')['xml_valid_message_exclude'];

        $this->container = new ServiceManager();
        $this->container->setService('config', $config);
        $this->container->setService('Config', $config);
        $this->container->setService('FilterManager', new FilterPluginManager($this->container, $config['filters']));
        $this->container->setService(
            'ValidatorManager',
            new ValidatorPluginManager($this->container, $config['validators'])
        );
        $this->container->setService(
            'TransExchangeXmlMapping',
            (new TransExchangeXmlFactory())($this->container, 'TransExchangeXmlMapping')
        );
    }

    /**
     * The registration is accepted and its licence, application type and circulated authorities are read from it
     */
    #[DataProvider('provideAcceptedExample')]
    public function testExampleIsAccepted(string $example): void
    {
        $xmlStructureInput = $this->validateXmlStructure($example);
        $this->assertSame([], $xmlStructureInput->getMessages());

        $document = $xmlStructureInput->getValue();
        $busRegistration = $this->readBusRegistration($document);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('txc', self::TXC_NAMESPACE);

        $licNo = $xpath->evaluate('string(//txc:LicensedOperator/txc:LicenceNumber)');
        $txcAppType = $xpath->evaluate('string(//txc:Registration/txc:ApplicationClassification)');

        $authorityNames = [];

        foreach ($xpath->query('//txc:CirculatedAuthority/txc:AuthorityName') as $authorityName) {
            $authorityNames[] = $authorityName->nodeValue;
        }

        $this->assertSame($licNo, $busRegistration['licNo']);
        $this->assertSame($txcAppType, $busRegistration['txcAppType']);
        $this->assertSame($authorityNames, $busRegistration['localAuthorities']);
    }

    public static function provideAcceptedExample(): array
    {
        return array_diff_key(self::provideExample(), self::EXPECTED_REJECTIONS);
    }

    /**
     * A real registration file with its circulated authorities and stops rewritten, read the way
     * EBSR reads it, then resolved against old authority records linked to their successors
     *
     * @param string[] $authorityNames the file's CirculatedAuthorities
     * @param string[] $expected txc names of the authorities the registration is attached to
     */
    #[DataProvider('provideRewrittenAuthorities')]
    public function testCirculatedAuthoritiesResolveFromTheFile(
        array $authorityNames,
        string $stopCode,
        array $expected,
        bool $valid
    ): void {
        $document = new DOMDocument();
        $document->load(self::apiDir() . self::EXAMPLES_DIR . '/linear/linear.xml');
        $this->rewriteAuthoritiesAndStops($document, $authorityNames, $stopCode);

        $xmlStructureInput = $this->validateXml($document->saveXML());
        $this->assertSame([], $xmlStructureInput->getMessages());

        $busRegistration = $this->readBusRegistration($xmlStructureInput->getValue());
        $this->assertSame([$stopCode => $stopCode], $busRegistration['naptan']);

        $directory = LocalAuthorityDirectory::withDefaultAuthorities();
        $fromStops = $directory->findByNaptanCodes($busRegistration['naptan']);
        $circulated = (new LocalAuthorityResolver())->resolveCirculated(
            $directory->findByTxcNames(LocalAuthorityResolver::normaliseNames($busRegistration['localAuthorities'])),
            $fromStops
        );

        $this->assertSame(
            $expected,
            array_map(static fn ($authority) => $authority->getTxcName(), $circulated->getValues())
        );
        $this->assertSame(
            $valid,
            (new LocalAuthorityMissing())->isValid([
                'localAuthoritys' => $circulated,
                'naptanAuthorities' => new ArrayCollection($fromStops),
            ])
        );
    }

    public static function provideRewrittenAuthorities(): array
    {
        return [
            'name only valid in 2.5.2, with the old stop code' => [['GreaterManchesterMCA'], '180', ['GreaterManchesterMCA'], true],
            'deprecated name with its old stop code' => [['Darlington'], '076', ['TeesValleyCA'], true],
            'old and new names for the same authority' => [['TeesValleyCA', 'Darlington'], '077', ['TeesValleyCA'], true],
            'old split name, stops renumbered to the new code' => [['Northamptonshire'], '305', ['NorthNorthamptonshire'], true],
            'new split name that misses the stops' => [['WestNorthamptonshire'], '305', ['WestNorthamptonshire'], false],
            'name padded with whitespace' => [[' Leicestershire '], '260', ['Leicestershire'], true],
            'stops in an authority not circulated' => [['Kent'], '076', ['Kent'], false],
        ];
    }

    #[DataProvider('provideRejectedExample')]
    public function testExampleIsRejected(string $example, string $reason): void
    {
        $xmlStructureInput = $this->validateXmlStructure($example, false);

        $this->assertStringContainsString($reason, implode("\n", $xmlStructureInput->getMessages()));
    }

    public static function provideRejectedExample(): array
    {
        $examples = [];

        foreach (array_intersect_key(self::provideExample(), self::EXPECTED_REJECTIONS) as $name => [$example]) {
            $examples[$name] = [$example, self::EXPECTED_REJECTIONS[$name]];
        }

        return $examples;
    }

    /**
     * An expected rejection naming an example that is no longer there would otherwise go unnoticed
     */
    public function testEveryExpectedRejectionIsAnExample(): void
    {
        $this->assertSame([], array_keys(array_diff_key(self::EXPECTED_REJECTIONS, self::provideExample())));
    }

    /**
     * Every example xml file, keyed by its path within the examples directory
     */
    private static function provideExample(): array
    {
        $dir = self::apiDir() . self::EXAMPLES_DIR;
        $examples = [];

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if ($file->getExtension() === 'xml') {
                $examples[substr($file->getPathname(), strlen($dir) + 1)] = [$file->getPathname()];
            }
        }

        ksort($examples);

        return $examples;
    }

    /**
     * Validates the example as part of a pack. A pack carries the documents and map its xml names, which the
     * examples ship without, so the pack gets empty stand-ins for them
     */
    private function validateXmlStructure(string $example, bool $expectValid = true): Input
    {
        return $this->validateXml(file_get_contents($example), $expectValid);
    }

    private function validateXml(string $xml, bool $expectValid = true): Input
    {
        $files = ['example.xml' => $xml];

        $document = new DOMDocument();
        $document->loadXML($xml);

        foreach (['DocumentUri', 'SchematicMap'] as $tagName) {
            foreach ($document->getElementsByTagName($tagName) as $element) {
                $files[$element->nodeValue] = '';
            }
        }

        $pack = vfsStream::setup('pack', null, $files);
        $xmlFilename = $pack->url() . '/example.xml';

        $input = (new XmlStructureInputFactory())($this->container, Input::class);
        $input->setValue($xmlFilename);

        $this->assertSame(
            $expectValid,
            $input->isValid(['xml_filename' => $xmlFilename]),
            implode("\n", $input->getMessages())
        );

        return $input;
    }

    /**
     * Runs the bus registration checks and mapping over a valid document, as EBSR does
     */
    private function readBusRegistration(DOMDocument $document): array
    {
        $this->moveOperatingPeriodIntoFuture($document);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('txc', self::TXC_NAMESPACE);

        $licNo = $xpath->evaluate('string(//txc:LicensedOperator/txc:LicenceNumber)');
        $txcAppType = $xpath->evaluate('string(//txc:Registration/txc:ApplicationClassification)');

        $busRegistrationInput = (new BusRegistrationInputFactory())($this->container, Input::class);
        $busRegistrationInput->setValue($document);

        $context = [
            'submissionType' => $txcAppType === 'nonChargeableChange'
                ? EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE
                : EbsrSubmission::NEW_SUBMISSION_TYPE,
            'organisation' => $this->organisationWithLicence($licNo),
        ];

        $this->assertTrue(
            $busRegistrationInput->isValid($context),
            implode("\n", $busRegistrationInput->getMessages())
        );

        return $busRegistrationInput->getValue();
    }

    /**
     * Replaces the circulated authorities with the given names, and moves every stop (referenced
     * or defined) to the given ATCO area code, the first three characters of its code
     *
     * @param string[] $authorityNames
     */
    private function rewriteAuthoritiesAndStops(DOMDocument $document, array $authorityNames, string $stopCode): void
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('txc', self::TXC_NAMESPACE);

        $circulatedAuthorities = $xpath->query('//txc:Registration/txc:CirculatedAuthorities')->item(0);

        while ($circulatedAuthorities->firstChild !== null) {
            $circulatedAuthorities->removeChild($circulatedAuthorities->firstChild);
        }

        foreach ($authorityNames as $authorityName) {
            $circulatedAuthority = $document->createElementNS(self::TXC_NAMESPACE, 'CirculatedAuthority');
            $circulatedAuthority->appendChild(
                $document->createElementNS(self::TXC_NAMESPACE, 'AuthorityName', $authorityName)
            );
            $circulatedAuthorities->appendChild($circulatedAuthority);
        }

        foreach ($xpath->query('//txc:StopPointRef | //txc:StopPoint/txc:AtcoCode') as $stopCodeNode) {
            $stopCodeNode->nodeValue = $stopCode . substr($stopCodeNode->nodeValue, 3);
        }
    }

    /**
     * The examples' services started years ago, and a new application must not start in the past. Moves the
     * operating period so it starts 60 days from now, keeping its length
     */
    private function moveOperatingPeriodIntoFuture(DOMDocument $document): void
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('txc', self::TXC_NAMESPACE);

        $startDate = new DateTimeImmutable($xpath->evaluate('string(//txc:Service/txc:OperatingPeriod/txc:StartDate)'));
        $days = (int) $startDate->diff(new DateTimeImmutable('today +60 days'))->format('%r%a');

        foreach ($xpath->query('//txc:Service/txc:OperatingPeriod/*') as $date) {
            $date->nodeValue = (new DateTimeImmutable($date->nodeValue))->modify(sprintf('%+d days', $days))
                ->format('Y-m-d');
        }
    }

    private function organisationWithLicence(string $licNo): Organisation
    {
        $organisation = new Organisation();

        $licence = new Licence($organisation, new RefData(Licence::LICENCE_STATUS_VALID));
        $licence->setLicNo($licNo);
        $licence->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));

        $organisation->setLicences(new ArrayCollection([$licence]));

        return $organisation;
    }

    private static function apiDir(): string
    {
        return dirname(__DIR__, 7);
    }
}
