<?php

namespace News\Controller;

use \Zend\Feed\Writer\Feed;
use \Zend\Feed\Writer\Writer as FeedWriter;

class NewsController extends \VuFind\Controller\AbstractBase
{

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */

    private function articleLink($guid=null) {
        $config = $this->getConfig();
        $link = $config->Site->url.'/news/article?guid='.$guid;
        return $link;
    }

    private function getExtension($filename=null) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext;
    }

    public function listAction()
    {
        $user = $this->getUser();
        $config = $this->getConfig();
        $admin = false;
        $newslist = null;
        $searchlist = null;
        if (isset($user->username)){
            if ($config->Social->newsadmin==$user->username)
                $admin =true;
        }

        if ($admin){

            $news = $this->getTable('news');
            $searchParam = $this->params()->fromPost('searchNews');
            $titleParam = $this->params()->fromPost('title');
            $idParam = $this->params()->fromQuery('id');
            $setParam = $this->params()->fromQuery('do');

            // save article
            if (!isset ($searchParam) && $titleParam != null) {

              $articleParams = $this->params()->fromPost();
                // generate guid hash if set is new
                if ($articleParams['id'] > 0) {
                    $guid = $articleParams['guid'];
                    $active = $articleParams['active'];
                    $pin = $articleParams['pin'];
                } else {
                    $guid = hash_hmac('md2', $titleParam . date("Y-m-d H:i:s") , 'fid4dfg');
                    $active = 0;
                    $pin = 0;
                }

                // get uploads
                $uploaddir = $config->Social->uploadnews;
                $fileDl1='';
                $fileDl2='';
                $fileDl3='';
                $filePic='';

                if (!empty($_FILES['dl1']['name'])) {
                    $fileDl1 = $guid.'_1.'.$this->getExtension($_FILES['dl1']['name']);
                    move_uploaded_file($_FILES['dl1']['tmp_name'], $uploaddir.$fileDl1);
                }

                if (!empty($_FILES['dl2']['name'])) {
                    $fileDl2 = $guid.'_2.'.$this->getExtension($_FILES['dl2']['name']);
                    move_uploaded_file($_FILES['dl2']['tmp_name'], $uploaddir.$fileDl2);
                }

                if (!empty($_FILES['dl3']['name'])) {
                    $fileDl3 = $guid.'_3.'.$this->getExtension($_FILES['dl3']['name']);
                    move_uploaded_file($_FILES['dl3']['tmp_name'], $uploaddir.$fileDl3);
                }

                if($articleParams['enddate'] == ''){
                    $enddate = '9999-12-31';

                } else {
                    $enddate = $articleParams['enddate'];
                }

                $data = array(
                        'title' => $titleParam,
                        'description' => $articleParams['description'],
                        'link' => $this->articleLink($guid),
                        'author' => $articleParams['author'],
                        'guid' => $guid,
                        'pubDate' => $articleParams['startdate'],
                        'category' => isset($articleParams['category']) ? $articleParams['category'] : '',
                        'enclosure' => isset($articleParams['enclosure']) ? $articleParams['enclosure'] : '',
                        'source' => $articleParams['source'],
                        'user' => 'Peter Parker',
                        'text' => $articleParams['text'],
                        'startdate' => $articleParams['startdate'],
                        'enddate' => $enddate,
                        'urlname1' => $articleParams['urlname1'],
                        'url1' => $articleParams['url1'],
                        'urlname2' => $articleParams['urlname2'],
                        'url2' => $articleParams['url2'],
                        'urlname3' => $articleParams['urlname3'],
                        'url3' => $articleParams['url3'],
                        'dlname1' => $articleParams['dlname1'],
                        'dl1' => $fileDl1,
                        'dlname2' => $articleParams['dlname2'],
                        'dl2' => $fileDl2,
                        'dlname3' => $articleParams['dlname3'],
                        'dl3' => $fileDl3,
                        'active' => $active,
                        'pin' => $pin
                );

                if ($articleParams['id'] > 0) {
                    $news->updateArticle($data,$articleParams['id']);
                } else {
                    $news->createArticle($data);
                }
            }

            // set actions
            if (isset ($setParam)) {
              switch ($setParam){
                    case 'act':
                        $news->switchValue('active',$idParam);
                        break;
                    case 'pin':
                        $news->switchValue('pin',$idParam);
                        break;
                    case 'del':
                        $news->deleteArticle($idParam);
                        break;
                }
            }

            // do search
            if (isset ($searchParam)) {
               $searchlist = $news->searchNews($searchParam);
            }

            // get all news
            $newslist = $news->getNewsList();
        }

        return $this->createViewModel(
                array
                (
                        'admin'    => $admin,
                        'newslist' => $newslist,
                        'searchlist' => $searchlist,
                )
        );

    }

    public function editAction($id=null)
    {
      $id = $this->params()->fromQuery('id');
      $news = $this->getTable('news');
      $article = $news->getArticleById($id);

      return $this->createViewModel(
              array
                  (
                        'article' => $article,
                  )
              );
    }

    /* Generate RSS Feed without Zend Feed Writer as it is not possible to set the
    source url according to rss 2.0 specification and to avoid unnecessary extensions.
    */
    public function rssAction()
    {
        $config = $this->getConfig();
        $url = $config->Site->url;

        $feed = new Feed();
        $feed->setTitle('Fachinformationsdienst Darstellende Kunst');
        $feed->setLink($url);
        $feed->setFeedLink($url . '/news/rss', 'atom');
        $feed->setDescription('An der Universitätsbibliothek Johann Christian Senckenberg (UB Frankfurt am Main) wird ab 2015 der
        Fachinformationsdienst (FID) Darstellende Kunst für die Theater- und Tanzwissenschaft aufgebaut. Hervorgegangen sind die
        von der DFG-geförderten Fachinformationsdienste für die Wissenschaft aus dem System der Sondersammelgebiete, die durch
        dieses Förderangebot abgelöst werden.');
        $feed->setLanguage('de-de');
        $feed->setCopyright('Fachinformationsdienst Darstellende Kunst');
        $feed->setDateModified(time());
        $feed->setImage(['uri' => 'https://www.performing-arts.eu/themes/fiddk/images/fid-dk_logo.gif','link' => $url,'title' => 'Fachinformationsdienst Darstellende Kunst']);

        $news = $this->getTable('news');
        $newslist = $news->getNewsList();

        foreach ($newslist as $news) {
          $entry = $feed->createEntry();
          $entry->setTitle(htmlspecialchars($news->title,ENT_QUOTES,"UTF-8"));
          $entry->setLink($url . '/news/article?guid=' . $news->guid);
          //$entry->setDescription(htmlspecialchars($news->description,ENT_QUOTES,"UTF-8"));
          $entry->setId($news->guid);
          $entry->setDateModified(time());
          $entry->setDateCreated(time());
          if ($news->enclosure) {
            if (explode('.',$news->enclosure)[1] == "gif") {
              $entry->setEnclosure(['uri'=> $url . '/themes/fiddk/images/' . htmlspecialchars($news->enclosure,ENT_QUOTES,"UTF-8"),'type'=> 'image/gif','length'=> 5070]);
            } else {
              $entry->setEnclosure(['uri'=> $url . '/themes/fiddk/images/' . htmlspecialchars($news->enclosure,ENT_QUOTES,"UTF-8"),'type'=> 'image/png','length'=> 5150]);
            }
          }
          $author = htmlspecialchars($news->author,ENT_QUOTES,"UTF-8");
          $name = str_replace(')','',explode('(',$author)[1]);
          $mail = trim(explode('(',$author)[0]);
          $entry->addAuthor(['name'=> $name,'email'=>$mail]);
          $entry->setDateCreated(date_create($news->startdate));
          $entry->addCategory(['term' => htmlspecialchars($news->category,ENT_QUOTES,"UTF-8")]);
          $entry->setCommentLink(htmlspecialchars($news->source,ENT_QUOTES,"UTF-8"));
          $feed->addEntry($entry);
      }

        $viewModel = $this->createViewModel(
                array(
                  'feed' => $feed
                ));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    /* Find and display an article by guid */
    public function articleAction()
    {
        $guid = $this->params()->fromQuery('guid');
        $news = $this->getTable('news');
        $article = $news->getArticle($guid);

        return $this->createViewModel(
                array
                    (
                          'article' => $article,
                    )
                );
    }

    public function currentAction()
    {
        $news = $this->getTable('news');
        $newslist = $news->getCurrentArticles();
        $pinnedlist = $news->getPinnedArticles();

        return $this->createViewModel(
                array
                    (
                        'newslist' => $newslist,
                        'pinnedlist' => $pinnedlist,
                    )
                );
    }

    public function newsAction()
    {
        return $this->currentAction();
    }

    public function archiveAction()
    {

      $news = $this->getTable('news');
      $newslist = $news->getArchivedArticles();

      return $this->createViewModel(
              array
                  (
                      'newslist' => $newslist
                  )
              );
    }

}
