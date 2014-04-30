<?php
/**
*	Journey View Helper
*
*	@author		Jess Rowbottom <jess@wrenthorpe.net>
*/
namespace SelfServe\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Journey extends AbstractHelper
{

	public function __invoke ($stage)
	{
        $applicationId = $this->getView()->params()->fromRoute('applicationId');
        $step = $this->getView()->params()->fromRoute('step');

        // collect completion status
        $statusArray = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        $output=print_r($statusArray,true);

        return $output;
    }

}

?>
