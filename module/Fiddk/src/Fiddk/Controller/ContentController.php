<?php
namespace Fiddk\Controller;

use Laminas\Mail\Address;

class ContentController extends \VuFind\Controller\ContentController
{
    protected $link = '';

    /**
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function licensedAction()
    {
        $licenseView = $this->createViewModel(
            [
              'link'    => $this->licenceLink($this->link),
            ]
        );

        // render page without vufind/fid layout
        $licenseView->setTerminal(true);

        return $licenseView;
    }

    public function licenceLink($licenceLink=null)
    {
        $config = $this->getConfig();
        $link = $config->Site->url . '/Content/licensed?link=' . $licenceLink;
        return $link;
    }

}
