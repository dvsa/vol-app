<?php

/**
 * Publication Helper - contains common methods to process and publish
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Service\Utility;

use \Common\Service\Data\PublicationLink;

/**
 * Publication Processing Helper
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PublicationHelper
{
    protected $publicationLinkService;

    protected $trafficAreaDataService;

    /**
     * Creates or updates a record using a data service
     *
     * @param array $data
     * @param string $filter
     * @return int
     */
    public function publish($data, $filter)
    {
        $publicationLink = $this->getPublicationLinkService()->createWithData($data);

        return $this->getPublicationLinkService()->createFromObject($publicationLink, $filter);
    }

    /**
     * Publish TM hearing. Multiple publishes, one per each Traffic Area and publication type.
     *
     * @param array $publishData
     * @param array $hearingData
     * @param string $filter
     */
    public function publishTm($publishData, $postedTrafficAreas, $postedPublicationTypes, $filter)
    {
        $trafficAreasToPublish = $this->getTrafficAreasToPublish($postedTrafficAreas);
        $publicationTypesToPublish = $this->getPublicationTypesToPublish($postedPublicationTypes);

        if (!empty($trafficAreasToPublish) && !empty($publicationTypesToPublish)) {
            foreach ($trafficAreasToPublish as $trafficArea) {
                foreach ($publicationTypesToPublish as $pubType) {
                    $publishData['pubType'] = $pubType;
                    $publishData['trafficArea'] = $trafficArea;
                    $this->publish(
                        $publishData,
                        $filter
                    );
                }
            }
        }
    }

    /**
     * Returns the publication types for the publication based on form data
     *
     * @param $hearingData
     * @return array
     */
    public function getPublicationTypesToPublish($postedPublicationTypes)
    {
        if (strtolower($postedPublicationTypes) == 'all') {
            $publicationTypesToPublish = ['A&D', 'N&P'];
        } else {
            $publicationTypesToPublish = [$postedPublicationTypes];
        }
        return $publicationTypesToPublish;
    }

    /**
     * Returns the traffic areas for the publication based on form data
     *
     * @param $hearingData
     * @return array
     */
    public function getTrafficAreasToPublish($postedTrafficAreas)
    {
        $trafficAreasToPublish = [];
        if (in_array('all', $postedTrafficAreas)) {
            // get all traffic areas
            $allTrafficAreas = $this->getTrafficAreaDataService()->fetchList();

            foreach ($allTrafficAreas as $ta) {
                $trafficAreasToPublish[] = $ta['id'];
            }
        } else {
            $trafficAreasToPublish = $postedTrafficAreas;
        }
        return $trafficAreasToPublish;
    }

    /**
     * Set publicationLinkService
     *
     * @param PublicationLink $publicationLinkService
     */
    public function setPublicationLinkService(PublicationLink $publicationLinkService)
    {
        $this->publicationLinkService = $publicationLinkService;
    }

    /**
     * Get publicationLinkService
     *
     * @return PublicationLink
     */
    public function getPublicationLinkService()
    {
        return $this->publicationLinkService;
    }

    /**
     * @param mixed $trafficAreaDataService
     */
    public function setTrafficAreaDataService($trafficAreaDataService)
    {
        $this->trafficAreaDataService = $trafficAreaDataService;
    }

    /**
     * @return mixed
     */
    public function getTrafficAreaDataService()
    {
        return $this->trafficAreaDataService;
    }
}
