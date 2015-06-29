<?php

/**
 * Example of using
 */

 // include Qymaen\Parser
include 'Parser.php';

// init parser
$parser = new Qymaen\Parser();

// parse some url
$parser->parse('http://example.com/');

// get data
$data = $parser->html->find('p', 1)->innertext;

// save data, method returns last insert row id
$lastInsertRowID = $parser->save(array('data' => $data));

// select data
$fetchAll = $parser->select(array('id' => $lastInsertRowID, 'fetchAll' => true));

// dump results
echo '<pre>'; print_r($fetchAll); echo '</pre>'; exit;