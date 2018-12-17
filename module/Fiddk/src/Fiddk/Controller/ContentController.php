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

    public function newsAction()
    {
      $view = $this->createViewModel();
      $view->email = $this->params()->fromPost('email');
      $view->action = $this->params()->fromPost('action');
      // Process form submission:
      if ($this->formWasSubmitted('submit')) {
        if (empty($view->email) || empty($view->action)) {
          $this->flashMessenger()->addMessage('bulk_error_missing', 'error');
          return;
        }

        // These settings are set in the feedback settion of your config.ini
        $config = $this->serviceLocator->get('VuFind\Config')->get('config');
        $feedback = isset($config->Feedback) ? $config->Feedback : null;
        $recipient_email = isset($feedback->recipient_email)
            ? $feedback->recipient_email : null;
        $recipient_name = isset($feedback->recipient_name)
            ? $feedback->recipient_name : 'Your Library';
        $newsletter_subject = isset($feedback->newsletter_subject)
            ? $feedback->newsletter_subject : 'VuFind Feedback';
        $sender_email = isset($feedback->sender_email)
            ? $feedback->sender_email : 'noreply@vufind.org';
        $sender_name = isset($feedback->sender_name)
            ? $feedback->sender_name : 'VuFind Feedback';
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
        return $view;
    }

    public function kaufvorschlagAction()
    {
      $view = $this->createViewModel();

      if ($this->formWasSubmitted('submit')) {

        $view->author = $this->params()->fromPost('author');
        $view->title = $this->params()->fromPost('title');
        $view->publisher = $this->params()->fromPost('publisher');
        $view->name = $this->params()->fromPost('name');
        $view->email = $this->params()->fromPost('email');
        //optional
        $view->isxn = $this->params()->fromPost('isxn');
        $view->placeOfPublication = $this->params()->fromPost('placeOfPublication');
        $view->dateOfPublication = $this->params()->fromPost('dateOfPublication');
        $view->remarks = $this->params()->fromPost('remarks');
        $view->institution = $this->params()->fromPost('institution');

        if (empty($view->author) || empty($view->title) || empty($view->publisher)
            || empty($view->name) || empty($view->email)) {
          $this->flashMessenger()->addMessage('bulk_error_missing', 'error');
          return $view;
        }

        // These settings are set in the feedback settion of your config.ini
        $config = $this->serviceLocator->get('VuFind\Config')->get('config');
        $feedback = isset($config->Feedback) ? $config->Feedback : null;
        $recipient_email = isset($feedback->recipient_email)
            ? $feedback->recipient_email : null;
        $recipient_name = isset($feedback->recipient_name)
            ? $feedback->recipient_name : 'Your Library';
        $kaufvorschlag_subject = isset($feedback->kaufvorschlag_subject)
            ? $feedback->kaufvorschlag_subject : 'VuFind Feedback';
        $sender_email = isset($feedback->sender_email)
            ? $feedback->sender_email : 'noreply@vufind.org';
        $sender_name = isset($feedback->sender_name)
            ? $feedback->sender_name : 'VuFind Feedback';
        if ($recipient_email == null) {
            throw new \Exception(
              'Feedback Module Error: Recipient Email Unset (see config.ini)'
            );
        }

        $email_message = "Kaufvorschlag:\n\n";
        $email_message .= "ISBN bzw. ISSN: ". $view->isxn . "\n";
        $email_message .= "Name Autor: ". $view->author . "\n";
        $email_message .= "Titel: ". $view->title . "\n";
        $email_message .= "Verlag: ". $view->publisher . "\n";
        $email_message .= "Erscheinungsort: ". $view->placeOfPublication . "\n";
        $email_message .= "Erscheinungsjahr: ". $view->dateOfPublication . "\n";
        $email_message .= "Anmerkungen: ". $view->remarks . "\n\n";
        $email_message .= "Name: ". $view->name . "\n";
        $email_message .= "E-Mail: ". $view->email . "\n";
        $email_message .= "Institution: ". $view->institution . "\n";

        // This sets up the email to be sent
        // Attempt to send the email and show an appropriate flash message:
        try {
           $mailer = $this->serviceLocator->get('VuFind\Mailer');
           $mailer->setMaxRecipients(2);
           $mailer->send(
             $mailer->stringToAddressList($recipient_email),
             new Address($sender_email, $sender_name),
             $kaufvorschlag_subject, $email_message
             );
           $this->flashMessenger()->addMessage(
             'Vielen Dank', 'success'
             );
          } catch (MailException $e) {
             $this->flashMessenger()->addMessage($e->getMessage(), 'error');
          }
        }
        return $view;
    }

    public function licenceLink($licenceLink=null)
    {
      $config = $this->getConfig();
      $link = $config->Site->url.'/Content/licensed?link='.$licenceLink;
      return $link;
    }


}
