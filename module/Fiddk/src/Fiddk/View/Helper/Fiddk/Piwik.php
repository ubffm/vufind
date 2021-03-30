<?php
/**
 * Piwik view helper
 *
 * PHP version 7
 *
 * Copyright (C) The National Library of Finland 2014-2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\View\Helper\Fiddk;

/**
 * Piwik Web Analytics view helper
 *
 * @category Fiddk
 * @package  View_Helpers
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class Piwik extends \VuFind\View\Helper\Root\Piwik
{

  /**
   * Get the Finalization Part of the Tracking code
   *
   * @return string JavaScript Code Fragment
   */
   protected function getClosingTrackingCode()
   {
     return <<<EOT
VuFindPiwikTracker.enableLinkTracking();
};
(function(){
if (typeof Piwik === 'undefined') {
    var d=document, g=d.createElement('script'),
        s=d.getElementsByTagName('script')[0];
    g.type='application/javascript'; g.name='matomo'; g.defer=true;
    g.async=true; g.src='{$this->url}piwik.js';
    g.onload=initVuFindPiwikTracker{$this->timestamp};
    s.parentNode.insertBefore(g,s);
} else {
    initVuFindPiwikTracker{$this->timestamp}();
}
})();
   EOT;
   }

}
