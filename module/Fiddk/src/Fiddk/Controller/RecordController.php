<?php
/**
 * Record Controller.
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
 * Record Controller.
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org Main Site
 */
class RecordController extends \VuFind\Controller\RecordController
{
   /**
   * Create a new ViewModel to use as an email form.
   *
   * @param array  $params         Parameters to pass to ViewModel constructor.
   * @param string $defaultSubject Default subject line to use.
   *
   * @return ViewModel
   */
  protected function createEmailViewModel($params = null, $defaultSubject = null)
  {
      // Build view:
      $view = $this->createViewModel($params);

      // Load configuration and current user for convenience:
      $config = $this->serviceLocator->get('VuFind\Config')->get('config');
      $view->disableFrom
          = (isset($config->Mail->disable_from) && $config->Mail->disable_from);
      $view->editableSubject = isset($config->Mail->user_editable_subjects)
          && $config->Mail->user_editable_subjects;
      $view->maxRecipients = isset($config->Mail->maximum_recipients)
          ? intval($config->Mail->maximum_recipients) : 1;
      $user = $this->getUser();

      $abbr = strtolower(substr($this->driver->getUniqueId(),0,3));
      $view->to = $config->Dprovider->dprov_to[$abbr];
      list($view->text,$view->text_en,$view->txt) = explode('|',$config->Dprovider->dprov_text->toArray()[$abbr]);
      // this is not so elegant... change record structure
      $view->callNumber = $this->params()->fromQuery('callNumber');

      // Send parameters back to view so form can be re-populated:
      if ($this->getRequest()->isPost()) {
          if (!$view->disableFrom) {
              $view->from = $this->params()->fromPost('from');
          }
          if ($view->editableSubject) {
              $view->subject = $this->params()->fromPost('subject');
          }
          $view->message = $this->params()->fromPost('message');
      }

      // Set default values if applicable:
      if ((!isset($view->to) || empty($view->to)) && $user
          && isset($config->Mail->user_email_in_to)
          && $config->Mail->user_email_in_to
      ) {
          $view->to = $user->email;
      }
      if (!isset($view->from) || empty($view->from)) {
          if ($user && isset($config->Mail->user_email_in_from)
              && $config->Mail->user_email_in_from
          ) {
              $view->userEmailInFrom = true;
              $view->from = $user->email;
          } elseif (isset($config->Mail->default_from)
              && $config->Mail->default_from
          ) {
              $view->from = $config->Mail->default_from;
          }
      }
      if (!isset($view->subject) || empty($view->subject)) {
          $view->subject = $defaultSubject;
      }

      // Fail if we're missing a from and the form element is disabled:
      if ($view->disableFrom) {
          if (empty($view->from)) {
              $view->from = $config->Site->email;
          }
          if (empty($view->from)) {
              throw new \Exception('Unable to determine email from address');
          }
      }

      return $view;
  }

}
