<?php
/**
 * Default Controller.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2010.
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
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Controller;

/**
 * Redirects the user to the appropriate default VuFind action.
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org Main Site
 */
class SearchController extends \VuFind\Controller\SearchController
{
    /**
     * Home action.
     *
     * @return mixed
     */
    public function homeAction()
    {
        $view = parent::homeAction();
        $view->options = $this->serviceLocator
            ->get('VuFind\SearchOptionsPluginManager')->get($this->searchClassId);
        $specialFacets = $this->parseSpecialFacetsSetting(
           $view->options->getSpecialAdvancedFacets()
        );
        $view->checkboxFacets = $this->processAdvancedCheckboxes(
          $specialFacets['checkboxes'], $view->saved
        );

        $runner = $this->serviceLocator->get('VuFind\SearchRunner');
        $date = date('Y-m-d');
        $request = ['lookfor' => $date,'type'=> 'playbills'];
        $view->results = $runner->run($request, $this->searchClassId, $this->getSearchSetupCallback());

        try {
          $news = $this->getTable('news');
          $view->newslist= $news->getCurrentArticles();
          $view->pinnedlist= $news->getPinnedArticles();
        } catch (\Exception $e) {
        }

        return $view;
    }
}
