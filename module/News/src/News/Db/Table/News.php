<?php
/**
 * Table Definition for news
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
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace News\Db\Table;

use VuFind\Db\Row\RowGateway;
use VuFind\Db\Table\PluginManager;
use Zend\Db\Adapter\Adapter;

/**
 * Table Definition for news
 *
 * @category VuFind
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class News extends \VuFind\Db\Table\Gateway
{

    /**
     * Constructor
     *
     * @param Adapter       $adapter       Database adapter
     * @param PluginManager $tm            Table manager
     * @param array         $cfg           Zend Framework configuration
     * @param RowGateway    $rowObj        Row prototype object (null for default)
     * @param string        $table         Name of database table to interface with
     */
    public function __construct(Adapter $adapter, PluginManager $tm, $cfg,
        RowGateway $rowObj = null, $table = 'news'
    ) {
        parent::__construct($adapter, $tm, $cfg, $rowObj, $table);
    }

    /*
     * Get article by guid
    */
    public function getArticle($guid)
    {
      return $this->select(['guid' => $guid])->current();
    }

    /*
     * Get article by id
    */
    public function getArticleById($id)
    {
      return $this->select(['id' => $id])->current();
    }

    /*
     * A list of all news articles
    */
    public function getNewsList()
    {
      $callback = function ($select) {
        $select->columns(['*']);
        $select->order('news.startdate DESC');
      };
      return $this->select($callback);
    }

    /*
     * Search through articles and find those macthing the searchTerm
    */
    public function searchNews($searchTerm)
    {
      $callback = function ($select) use ($searchTerm) {
        $select->columns(['*']);
        $select->where->like('title','%'.$searchTerm.'%')->or->like('text','%'.$searchTerm.'%');
        $select->order('news.startdate DESC');
      };
      return $this->select($callback);
    }

    /*
     * A list of current articles
    */
    public function getCurrentArticles()
    {
      $callback = function ($select) {
        $select->columns(['*']);
        $select->where(array('pin != true', 'active = true','startdate<=NOW()','enddate>=NOW() '));
        $select->order('news.startdate DESC');
      };
      return $this->select($callback);
    }

    /*
     * A list of pinned articles
    */
    public function getPinnedArticles()
    {
      $callback = function ($select) {
        $select->columns(['*']);
        $select->where(array('pin = true', 'active = true','startdate<=NOW()','enddate>=NOW() '));
        $select->order('news.startdate DESC');
      };
      return $this->select($callback);
    }

    /*
     * A list of archived articles
    */
    public function getArchivedArticles()
    {
      $callback = function ($select) {
        $select->columns(['*']);
        $select->where(array('active = true','enddate<=NOW()'));
        $select->order('news.startdate DESC');
      };
      return $this->select($callback);
    }

    /*
     * Update a certain news article
    */
    public function updateArticle($data,$id)
    {
      $callback = function ($select) use ($id) {
           $select->where->equalTo('id', $id);
       };
       $this->update($data, $callback);
    }

    /*
     * Create a new news article
    */
    public function createArticle($data)
    {
      return $this->insert($data);
    }

    /*
     * Delete article by id
    */
    public function deleteArticle($id)
    {
      $callback = function ($select) use ($id) {
           $select->where->equalTo('id', $id);
       };
       $this->delete($callback);
    }

    public function switchValue($value,$id)
    {
      $article = $this->getArticleById($id);
      if(isset($article[$value])) {
          if($article[$value] == 0){
              $set = array($value => 1);
          } else {
              $set = array($value => 0);
          }
      } else {
          $set = array($value => 1);
      }
      return $this->updateArticle($set,$id);
    }
}
