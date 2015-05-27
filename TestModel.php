<?php

require_once "DB.php";
require_once "Model1.php";

use Db;

class TestModel {

    public function __construct() {
        
    }

    public function Main() {
        //DB object
        $db = new Db();
        // Get Stock prices
        $m1 = new Model1();

        //Check when was the last update
        $sql = "SELECT FC_WIKI_Codes.Code\n"
                . " FROM FC_WIKI_Codes\n"
                . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID\n"
                . " GROUP BY FC_WIKI_Codes.Code ";
        $result = $db->select($sql);


        $gain = 0;

        for ($i = 0; $i <= count($result) - 1; $i++) {
            $gain = $gain + $m1->Main($result[$i]['Code']);
        }
        //print "total gain - $gain";
    }

    public function PrintResults() {
        $db = new Db();
        $sql = "SELECT FC_WIKI_Codes.Code, Replace(FC_WIKI_Codes.Name, 'Prices, Dividends, Splits and Trading Volume', '') AS `Name` , SUM(Sell_Price - Purchase_Price ) AS Profit\n"
                . "FROM `FC_Results` \n"
                . "INNER JOIN FC_WIKI_Codes ON FC_WIKI_Codes.ID = FC_Results.Stock_ID\n"
                . "GROUP BY FC_WIKI_Codes.Code, FC_WIKI_Codes.Name\n"
                . "ORDER BY FC_WIKI_Codes.Code, FC_WIKI_Codes.Name ASC";
        $result = $db->select($sql);

        for ($i = 0; $i <= count($result) - 1; $i++) {
            print "{$result[$i]['Code']}, {$result[$i]['Name']}: {$result[$i]['Profit']} <br>";
        }
        
        $sql = "SELECT SUM(Sell_Price - Purchase_Price ) AS Profit FROM `FC_Results`";
        $result = $db->select($sql);
        print "Total - {$result[0]['Profit']}";
    }

    public function CalculateProfit() {
        //How much to invest
        $funds_allocated = 1000;

        //DB object
        $db = new Db();        //Get the list of stocks
        $sql = "SELECT * FROM `FC_Results` ORDER BY `Purchase_Date` ASC";
        $result = $db->select($sql);

        for ($i = 0; $i <= count($result) - 1; $i++) {
            $num_of_stocks = 1000 / $result[$i]['Purchase_Price'];
            $profit = (int) $num_of_stocks * $result[$i]['Sell_Price'] - (int) $num_of_stocks * $result[$i]['Purchase_Price'];
            $sql = "UPDATE `FC_Results` SET Profit = $profit WHERE ID = {$result[$i]['ID']}";
            $db->query($sql);
        }
    }

    public function InvestmentStrategy() {
        //How much investment
        $funds_allocated = 10000;

        //Date settings
        $begin = new DateTime('2014-03-31');
        $end = new DateTime('2015-03-31');
        $end = $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        //DB object
        $db = new Db();
        // Get Stock prices
        $d_box = new Model1();

        //Get the list of stocks
        $sql = "SELECT FC_WIKI_Codes.Code\n"
                . " FROM FC_WIKI_Codes\n"
                . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID\n"
                . " GROUP BY FC_WIKI_Codes.Code ";
        $result = $db->select($sql);

        foreach ($daterange as $date) {
            //check if it's a trade date if yes then.. 
            //loop through the stocks to find a buying point, buy, and see how much it gained. 
            //and from what date to what date.
            for ($i = 0; $i <= count($result) - 1; $i++) {
                $d_box->BuyByDate($result[$i]['Code'], $date->format("Y-m-d"), $gain, $date);
            }
            //echo $date->format("Ymd") . "<br>";
        }
    }

}

?>