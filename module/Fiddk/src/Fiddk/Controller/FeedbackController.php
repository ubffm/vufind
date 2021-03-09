<?php
/**
 * Controller for configurable forms (feedback etc).
 *
 * PHP version 7
 *
 * @category VuFind
 * @package  Controller
 * @author   Josiah Knoll <jk1135@ship.edu>
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Controller;

/**
 * Controller for configurable forms (feedback etc).
 *
 * @category VuFind
 * @package  Controller
 * @author   Josiah Knoll <jk1135@ship.edu>
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class FeedbackController extends \VuFind\Controller\FeedbackController
{
    /**
     * Handles rendering and submit of dynamic forms.
     * Form configurations are specified in FeedbackForms.json
     *
     * @return void
     */
    public function formAction()
    {
        $formId = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
        if (!$formId) {
            $formId = 'FeedbackSite';
        }

        $user = $this->getUser();

        $form = $this->serviceLocator->get($this->formClass);
        $params = [];
        if ($refererHeader = $this->getRequest()->getHeader('Referer')
        ) {
            $params['referrer'] = $refererHeader->getFieldValue();
        }
        $form->setFormId($formId, $params);

        if (!$form->isEnabled()) {
            throw new \VuFind\Exception\Forbidden("Form '$formId' is disabled");
        }

        if (!$user && $form->showOnlyForLoggedUsers()) {
            return $this->forceLogin();
        }

        $view = $this->createViewModel(compact('form', 'formId', 'user'));
        $view->useCaptcha
            = $this->captcha()->active('feedback') && $form->useCaptcha();

        $params = $this->params();
        $form->setData($params->fromPost());

        if (($formId == 'AskArchiveDTK' || $formId == 'AskArchiveMCB') && !$this->formWasSubmitted('submit', $view->useCaptcha)) {
            $callNumber = $this->params()->fromRoute('callNumber', $this->params()->fromQuery('callNumber'));
            $form = $this->prefillRecordInfo($form, $callNumber);
            return $view;
        }

        if (!$this->formWasSubmitted('submit', $view->useCaptcha)) {
            $form = $this->prefillUserInfo($form, $user);
            return $view;
        }

        if (! $form->isValid()) {
            return $view;
        }

        list($messageParams, $template)
            = $form->formatEmailMessage($this->params()->fromPost());
        $emailMessage = $this->getViewRenderer()->partial(
            $template, ['fields' => $messageParams]
        );

        list($senderName, $senderEmail) = $this->getSender();

        $replyToName = $params->fromPost(
            'name',
            $user ? trim($user->firstname . ' ' . $user->lastname) : null
        );
        $replyToEmail = $params->fromPost(
            'email',
            $user ? $user->email : null
        );

        $recipients = $form->getRecipient($params->fromPost());

        $emailSubject = $form->getEmailSubject($params->fromPost());

        $sendSuccess = true;
        foreach ($recipients as $recipient) {
            list($success, $errorMsg) = $this->sendEmail(
                $recipient['name'], $recipient['email'], $senderName, $senderEmail,
                $replyToName, $replyToEmail, $emailSubject, $emailMessage
            );

            $sendSuccess = $sendSuccess && $success;
            if (!$success) {
                $this->showResponse(
                    $view, $form, false, $errorMsg
                );
            }
        }

        if ($sendSuccess) {
            $this->showResponse($view, $form, true);
        }

        return $view;
    }

    /**
     * Prefill form callNumber field for ask archive requests
     *
     * @param Form  $form Form
     * @param array $callNumber
     *
     * @return Form
     */
    protected function prefillRecordInfo($form, $callNumber)
    {
        if ($callNumber) {
            $form->setData(
                [
                 'CallNumber' => $callNumber
                ]
            );
        }
        return $form;
    }

}
