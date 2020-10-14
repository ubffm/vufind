<?php

namespace Fiddk\Controller;

use Zend\Mail;
use Zend\Mail\Address;
use Zend\Mail\AddressList;

class ContentController extends \VuFind\Controller\ContentController
{
    protected $link = '';

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */

    public function licensedAction()
    {
      $licenseView = $this->createViewModel(
          array(
              'link'    => $this->licenceLink($this->link),
      ));

      // render page without vufind/fid layout
      $licenseView->setTerminal(true);

      return $licenseView;
    }

    public function licenceLink($licenceLink=null)
    {
      $config = $this->getConfig();
      $link = $config->Site->url.'/Content/licensed?link='.$licenceLink;
      return $link;
    }

    public function contentAction()
    {
      $page = $this->params()->fromRoute('page');
      $themeInfo = $this->serviceLocator->get(\VuFindTheme\ThemeInfo::class);
      $language = $this->serviceLocator->get(\Zend\Mvc\I18n\Translator::class)
    ->getLocale();
    $defaultLanguage = $this->getConfig()->Site->language;
    // Try to find a template using
    // 1.) Current language suffix
    // 2.) Default language suffix
    // 3.) No language suffix
    $currentTpl = "templates/content/{$page}_$language.phtml";
    $defaultTpl = "templates/content/{$page}_$defaultLanguage.phtml";
    if (null !== $themeInfo->findContainingTheme($currentTpl)) {
      $page = "{$page}_$language";
    } elseif (null !== $themeInfo->findContainingTheme($defaultTpl)) {
      $page = "{$page}_$defaultLanguage";
    }
    if (empty($page) || 'content' === $page
        || null === $themeInfo->findContainingTheme(
        "templates/content/$page.phtml"
    )
    ) {
      return $this->notFoundAction($this->getResponse());
    }
    $view = $this->createViewModel(['page' => $page]);
    // if news page then check if form was submitted
    if ($page == 'news') {
          if ($this->formWasSubmitted('submit')) {
            $view->email = $this->params()->fromPost('email');
            $view->action = $this->params()->fromPost('action');
            if (empty($view->email) || empty($view->action)) {
              $this->flashMessenger()->addMessage('bulk_error_missing', 'error');
              return $view;
            }
            $validator = new \Zend\Validator\EmailAddress();
            if (!$validator->isValid($view->email)) {
              $this->flashMessenger()->addMessage('Email address is invalid', 'error');
              return $view;
            }

            // These settings are set in the feedback settion of your config.ini
            $config = $this->serviceLocator->get('VuFind\Config')->get('config');
            $feedback = isset($config->Feedback) ? $config->Feedback : null;
            $recipient_email = isset($feedback->recipient_email)
                ? $feedback->recipient_email : null;
            $recipient_name = isset($feedback->recipient_name)
                ? $feedback->recipient_name : 'UB Frankfurt';
            $newsletter_subject = isset($feedback->newsletter_subject)
                ? $feedback->newsletter_subject : 'Newsletter Subscription';
            $sender_email = isset($feedback->sender_email)
                ? $feedback->sender_email : 'redaktion@performing-arts.eu';
            $sender_name = isset($feedback->sender_name)
                ? $feedback->sender_name : 'Newsletter Subscription';
            if ($recipient_email == null) {
                throw new \Exception(
                  'Feedback Module Error: Recipient Email Unset (see config.ini)'
                );
            }

            $email_message = 'Email: ' . $view->email . "\n";
            $email_message .= '----> ' . $view->action . "\n\n";

            // This sets up the email to be sent
            // Attempt to send the email and show an appropriate flash message:
            try {
               $mailer = $this->serviceLocator->get('VuFind\Mailer');
               $mailer->setMaxRecipients(2);
               $mailer->send(
                 $mailer->stringToAddressList($recipient_email),
                 new Address($sender_email, $sender_name),
                 $newsletter_subject, $email_message
                 );
               $this->flashMessenger()->addMessage(
                 'Vielen Dank', 'success'
                 );
              } catch (MailException $e) {
                 $this->flashMessenger()->addMessage($e->getMessage(), 'error');
              }
            }
        }
        return $view;
    }

}
