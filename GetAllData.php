<?php

require_once "GetStockPrices.php";

// Set variables
$api_key = "eBkx1Ed14Nkz8shyEJ9g";
//$stock_symbol = $_GET['stock-symbol'];
//$symbol = "WIKI/$stock_symbol";

// Get Stock prices
$sp = new GetStockPrices();
//Create DB object
$db = new DB();

$sql = "SELECT * FROM `FC_WIKI_Codes` WHERE 1 \n"
    . "ORDER BY `FC_WIKI_Codes`.`ID` ASC ";
$result = $db->select($sql);

//INSERT All data to database
$i = 0;
for ($i = 0; $i <= count($result) - 1; $i++) {
    $stock_symbol = $result[$i]['Code'];
    $sData = $sp->GetData($stock_symbol);
}

?>