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
   * Returns Piwik code (if active) or empty string if not.
   *
   * @param array $params Parameters
   *
   * @return string
   */
  public function __invoke($params = null)
  {
      if (!$this->url) {
          return '';
      }

      $this->params = $params;
      if (isset($this->params['lightbox'])) {
          $this->lightbox = $this->params['lightbox'];
      }

      $results = $this->getSearchResults();
      if ($results && ($combinedResults = $this->getCombinedSearchResults())) {
          $code = $this->trackCombinedSearch($results, $combinedResults);
      } elseif ($results) {
          $code = $this->trackSearch($results);
      } elseif ($recordDriver = $this->getRecordDriver()) {
          $code = $this->trackRecordPage($recordDriver);
      } else {
          $code = $this->trackPageView();
      }

      $inlineScript = $this->getView()->plugin('inlinescript');
      // needed, as normally only W3C approved attributes are allowed
      $inlineScript->setAllowArbitraryAttributes(true);
      return $inlineScript(\Laminas\View\Helper\HeadScript::SCRIPT, $code, 'SET', ['data-type' => 'application/javascript','data-name' => 'matomo'],'text/plain');
  }
}
