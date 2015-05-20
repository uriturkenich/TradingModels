<?php

require_once "DB.php";

use Db;

class DarvasBox {

    public function __construct() {
        
    }

    public function CreateBox($stock_symbol) {
        $db = new Db();
        $last_low = 0;
        $last_high = 999999;
        $second_low = 999999;
        $follow_up = 0;
        $STOP_price = 0;
        $purchace_price = 0;
        $gain = 0;
        $bought = 0;

        //Check when was the last update
        $sql = "SELECT FC_Stock_Prices.ID, FC_Stock_Prices.Trade_Date, High, Low "
                . " FROM FC_WIKI_Codes \n"
                . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID \n"
                . " WHERE Trade_Date >= date_sub(NOW(), INTERVAL 500 DAY) AND FC_WIKI_Codes.Code = '$stock_symbol'"
                . " ORDER BY Trade_date";
        $result = $db->select($sql);
        
        //LOOP through the prices until the day before the last
        for ($i = 1; $i <= count($result) - 2; $i++) {
            
            //if the price is higher then follow up then buy
            if ($follow_up == 1 && $result[$i]['High'] > $purchace_price && $bought == 0){
                print "Bought - {$result[$i]['Trade_Date']} ";
                $bought = 1;
                $STOP_price = $last_low;
            }
            
            //if the price is lower then STOP point then sell
            if ($follow_up == 1 && $result[$i]['Low'] < $STOP_price && $bought == 1){
                print "Sold - {$result[$i]['Trade_Date']}, stop - $STOP_price, purchse - $purchace_price <br>";
                $follow_up = 0;
                $bought = 0;
                $gain = $gain + $STOP_price - $purchace_price ;
            }
            
            //Local LOW
            if ($result[$i-1]['Low'] > $result[$i]['Low'] && $result[$i+1]['Low'] > $result[$i]['Low']){
                //SECOND LOW
                if ($last_low > $result[$i]['Low']){
                    //print "second low - {$result[$i]['Trade_Date']}";
                    $second_low = $last_low;
                    $follow_up = 0;
                    $last_high = 999999;
                    $sql = "INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 2, fc_stockid)";
                }
                //FIRST LOW
                else{
                    //If Follow up point was found calculate STOP point
                    if ($follow_up == 1){
                        $STOP_price = $result[$i]['Low'];
                    }
                    $sql= "INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 1, fc_stockid)";
                }
                //update last low
                $last_low = $result[$i]['Low'];
                //print "first low - {$result[$i]['Trade_Date']}";
            }
            
            //Local HIGH
            if ($result[$i-1]['High'] < $result[$i]['High'] && $result[$i+1]['High'] < $result[$i]['High'] && $result[$i]['High'] > $second_low && $bought ==0){
                //FOLLOW UP POINT
                if ($last_high < $result[$i]['High']){
                    print "follow up - {$result[$i]['Trade_Date']} ";
                    $last_high = 999999;
                    $follow_up = 1;
                    $bought = 0;
                    $purchace_price = $result[$i]['High'];
                }
                //FIRST HIGH
                else{
                    $last_high = $result[$i]['High'];
                    //print "first high - {$result[$i]['Trade_Date']} \n";
                }
            }
        }
        
        print "Gain - $gain";
        
    }


}
