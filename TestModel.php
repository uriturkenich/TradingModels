<?php

require_once "DB.php";
require_once "DarvasBox.php";

use Db;

class TestModel {

    public function __construct() {
        
    }

    public function Main($stock_symbol) {
        $db = new Db();
        //Check when was the last update
        $sql = "SELECT FC_WIKI_Codes.Code\n"
                . " FROM FC_WIKI_Codes\n"
                . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID\n"
                . " GROUP BY FC_WIKI_Codes.Code LIMIT 15";
        $result = $db->select($sql);

        // Get Stock prices
        $d_box = new Model1();

        for ($i = 0; $i <= count($result) - 1; $i++) {
            $d_box->Main($result[$i]['Code']);
        }
    }
}

?>