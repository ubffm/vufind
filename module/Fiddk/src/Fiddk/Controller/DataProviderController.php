<?php
/**
 * Data provider Controller
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2011.
 * Copyright (C) The National Library of Finland 2014-2016.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Controller
 * @author   Julia Beck
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
namespace Fiddk\Controller;

/**
 * Controller for mostly static pages that doesn't fall under any particular
 * function.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class DataProviderController extends \VuFind\Controller\AbstractBase
{
    /**
     * Default action if none provided
     *
     * @return Laminas\View\Model\ViewModel
     */
    public function dataProviderAction()
    {
        $page = $this->params()->fromRoute('page');
        $themeInfo = $this->serviceLocator->get('VuFindTheme\ThemeInfo');
        $language = "de";
        //$this->serviceLocator->get('Laminas\Mvc\I18n\Translator')
        //  ->getLocale();
        $defaultLanguage = $this->getConfig()->Site->language;
        // Try to find a template using
        // 1.) Current language suffix
        // 2.) Default language suffix
        // 3.) No language suffix
        $currentTpl = "templates/dataprovider/{$page}_$language.phtml";
        $defaultTpl = "templates/dataprovider/{$page}_$defaultLanguage.phtml";
        if (null !== $themeInfo->findContainingTheme($currentTpl)) {
            $page = "{$page}_$language";
        } elseif (null !== $themeInfo->findContainingTheme($defaultTpl)) {
            $page = "{$page}_$defaultLanguage";
        }
        if (empty($page) || 'dataprovider' === $page
            || null === $themeInfo->findContainingTheme(
                "templates/dataprovider/$page.phtml"
            )
        ) {
            return $this->notFoundAction($this->getResponse());
        }
        $view = $this->createViewModel(['page' => $page]);
        return $view;
    }

}
