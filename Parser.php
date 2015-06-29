<?php

/**
 * Web-site parser
 * @uses Simple HTML DOM Parser http://sourceforge.net/projects/simplehtmldom/
 * 
 * 
 * 
 * 
 */

namespace Qymaen;

class Parser
{
  /**
   * used Parser
   */
  protected $_parser = 'simple_html_dom';
  
  /**
   * used DB type
   */
  protected $_dbType = 'SQLite';
  
  /**
   * used DB
   */
  protected $_db;
  
  /**
   * Parsed Page, simple_html_dom Object (as example)
   */
  public $html;
  
  /**
   * Url of parsed page
   */
  protected $_url;
  
  /**
   * Initialize Parser
   * Initialize DB
   *
   * @return Parser $parser
   */
  public function __construct()
  {
    $this->initParser();
    $this->initDb();
  }
  
  /**
   * Initialization Parser libruary
   */
  public function initParser()
  {
    if ($this->_parser === 'simple_html_dom') {
      require_once 'lib/Simple_HTML_DOM/simple_html_dom.php';
    }
    /* here can be another parser to use */
  }
  
  /**
   * Initialization DB
   */
  public function initDb()
  {
    if ($this->_dbType === 'SQLite') {
      
      // create DB
      $this->_db = new \SQLite3('qymaen_parser.db');
      
      // create table
      $this->_db->exec('CREATE TABLE IF NOT EXISTS parsed_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        page_url TEXT NOT NULL,
        data LONGTEXT NOT NULL,
        params LONGTEXT NULL,
        creation_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
      )');
    }
    /* here can be another DB to use */
  }
  
  /**
   * Parse page at some $url
   * @param string $url
   * @param array $params
   *
   * @return void
   */
  public function parse($url, $params = array())
  {
    // save url
    $this->_url = $url;
    
    // parse
    if ($this->_parser === 'simple_html_dom') {
      $this->html = file_get_html($url);
    }
    /* here can be another parser function/method to use */
  }
  
  /**
   * Save data into DB
   * @param array $data
   * @param array $params
   *
   * @return int $lastInsertRowId
   */
  public function save($data, $params = array())
  {
    $defaultData = array(
      'page_url' => $this->_url,
      'params' => '',
    );
    
    $data = array_merge($defaultData, $data);
    
    // Serialize params as JSON string
    if (!empty($data['params'])) {
      $data['params'] = json_encode($data['params']);
    }
    
    // data is required to save
    if (empty($data['data'])) {
      throw new Exception('data required to save!');
    }
    
    // Choose DB
    if ($this->_dbType === 'SQLite') {
      // prepare
      $stmt = $this->_db->prepare("INSERT INTO parsed_data (page_url, data, params) VALUES (?, ?, ?)");
      
      // bind
      $stmt->bindValue(1, $data['page_url'], SQLITE3_TEXT);
      $stmt->bindValue(2, $data['data'], SQLITE3_TEXT);
      $stmt->bindValue(3, $data['params'], SQLITE3_TEXT);
      
      // execute
      $result = $stmt->execute();
      
      return $this->_db->lastInsertRowID();
    }
    /* here can be another DB to use */
    
    return false;
  }
  
  /**
   * Select record from DB
   * @param array $params
   *
   * @return mixed $row
   */
  public function select($params = array())
  {
    $result = false;
    
    // Choose DB
    if ($this->_dbType === 'SQLite') {
      
      if (!empty($params['id'])) {
        $stmt = $this->_db->prepare('SELECT *, rowid FROM parsed_data WHERE id=:id');
        $stmt->bindValue(':id', $params['id'], SQLITE3_INTEGER);
        
        $result = $stmt->execute();
      } else {
        $stmt = $this->_db->prepare('SELECT *, rowid FROM parsed_data');
        
        $result = $stmt->execute();
      }
      
      // fetch all
      if (!empty($params['fetchAll'])) {
        $fetchAll = array();
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
          $fetchAll[] = $row;
        }
        
        return $fetchAll;
      }
      
      return $result;
    }
    /* here can be another DB to use */
    
    return false;
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
}