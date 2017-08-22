<?php

namespace fiddk\Controller;

use Zend\Mail;

class SpagesController extends \VuFind\Controller\AbstractBase
{

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */

    public function uberAction()
    {
        return $this->createViewModel();
    }

    public function beiratAction()
    {
        return $this->createViewModel();
    }

    public function netzwerkeAction()
    {
        return $this->createViewModel();
    }

    public function netzwerke2Action()
    {
        return $this->createViewModel();
    }

    public function netzwerke3Action()
    {
        return $this->createViewModel();
    }

    public function dokumentationAction()
    {
        return $this->createViewModel();
    }

    public function trailerAction()
    {
        return $this->createViewModel();
    }

    public function downloadsAction()
    {
        return $this->createViewModel();
    }

    public function kontaktAction()
    {
        return $this->createViewModel();
    }

    public function suchenAction()
    {
        return $this->createViewModel();
    }

    public function faqAction()
    {
        return $this->createViewModel();
    }

    public function fiddkLicensedContentAction()
    {
      $licenseView = new ViewModel(array(
        'link'    => $this->licenceLink($link),
      ));

      // render page without vufind/fid layout
      $licenseView->setTerminal(true);

      return $licenseView;
    }

    public function newsAction()
    {
        $display_message = false;
        $message = '';
        // if there is POST process it!
        if ($_POST != null) {

            if ((!isset ($_POST['action'])) || (!isset ($_POST['email']))) {
                $display_message = true;
                $message = 'Choose Option';
            }  else {
                $validator = new \Zend\Validator\EmailAddress();
                if ($validator->isValid($_POST['email'])) {
                    // Ok, send this request
                    $config = $this->getConfig();
                    $body= $_POST['action'].'
---->
'.$_POST['email'];
                    $mail = new Mail\Message();
                    $mail->setBody($body);
                    $mail->setFrom($config->Feedback->sender_email, $config->Feedback->sender_name);
                    $mail->addTo($config->Feedback->recipient_email, $config->Feedback->recipient_name);
                    $mail->setSubject($config->Feedback->recipient_email, $config->Feedback->newsletter_subject);
                    $transport = new Mail\Transport\Sendmail();
                    $transport->send($mail);
                    $display_message = true;
                    $message = 'Success Mail';

                } else {
                    foreach ($validator->getMessages() as $mes) {
                        $display_message = true;
                        $message = 'Invalid Address';
                    }
                }
            }
        }
        return $this->createViewModel(
                    array
                    (
                            'display_message'    => $display_message,
                            'message' => $message,
                    )
                );
    }

    public function themenAction()
    {
        return $this->createViewModel();
    }

    public function contentsAction()
    {
        return $this->createViewModel();
    }

    public function neuerwerbAction()
    {
        return $this->createViewModel();
    }

    public function fernleiheAction()
    {
        return $this->createViewModel();
    }

    public function kaufvorschlagAction()
    {
        $display_message = false;
        $message = '';
        $inputs = [];
        // if there is POST process it!
        if ($_POST != null) {

            if (
                    empty($_POST['Name_Autor']) ||
                    empty($_POST['Titel']) ||
                    empty($_POST['Verlag']) ||
                    empty($_POST['Name']) ||
                    empty($_POST['Vorname']) ||
                    empty($_POST['email'])
                    ) {
                $display_message = true;
                $message = 'Input missing';
                $inputs = $_POST;

            }  else {
                $validator = new \Zend\Validator\EmailAddress();
                if ($validator->isValid($_POST['email'])) {
                    // Ok, send this request
                    $config = $this->getConfig();
                    $body=
                    'ISBN bzw. ISSN:
'.$_POST['ISBN']
.'
---
Name des Autors:
'.$_POST['Name_Autor']
.'
---
Vorname des Autors:
'.$_POST['Vorname_Autor']
.'
---
Titel:
'.$_POST['Titel']
.'
---
Verlag:
'.$_POST['Verlag']
.'
---
Erscheinungsort:
'.$_POST['Erscheinungsort']
.'
---
Jahr:
'.$_POST['Erscheinungsjahr']
.'
---
Anmerkungen:
'.$_POST['Anmerkungen']
.'
---
Name:
'.$_POST['Name']
.'
---
Vorname:
'.$_POST['Vorname']
.'
---
E-Mail:
'.$_POST['email']
.'
---
Institution:
'.$_POST['institution'];

                    $mail = new Mail\Message();
                    $mail->setBody($body);
                    $mail->setFrom($config->Feedback->sender_email, $config->Feedback->sender_name);
                    $mail->addTo($config->Feedback->recipient_email, $config->Feedback->recipient_name);
                    $mail->setSubject($config->Feedback->kaufvorschlag_subject);
                    $transport = new Mail\Transport\Sendmail();
                    $transport->send($mail);
                    $display_message = true;
                    $message = 'Success Mail';

                } else {
                    foreach ($validator->getMessages() as $mes) {
                        $display_message = true;
                        $message = 'Invalid Address';
                        $inputs = $_POST;
                    }
                }
            }
        }
        return $this->createViewModel(
                    array
                    (
                            'display_message'    => $display_message,
                            'message' => $message,
                            'inputs' => $inputs,
                    )
                );
    }

    public function impressumAction()
    {
        return $this->createViewModel();
    }

    public function copyrightAction()
    {
        return $this->createViewModel();
    }

    public function verweiseAction()
    {
        return $this->createViewModel();
    }

    public function datenschutzAction()
    {
        return $this->createViewModel();
    }

    public function haftungAction()
    {
        return $this->createViewModel();
    }

    public function licenceLink($licenceLink=null)
    {
      $config = $this->getConfig();
      $link = $config->Site->url.'/spages/fiddk-licensed-content?link='.$licenceLink;
      return $link;
    }


}
