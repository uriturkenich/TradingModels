<?php
/* MONEY FLOW ALGORITHEM
 * 1. FOLLOW UP POINT (FUP) is created when - There are two (or more) consequative "local lows" and then two consequative "local highs".
 * 2. BUY the stock if the price is higher then FUP and the following conditions are true:
 * 2.1. BUY only if Money Flow INDEX is >45 and <60 
 * 2.2. BUY only if Price>AVG(50)
 * 3. EXIT Strategy... 
 * 4. In case there are two consequative lows. Go to step 1.
*/
require_once "DB.php";

use Db;

class Model1 {

    public $MFLength = 15;

    public function __construct() {
        
    }

    public function Main($stock_symbol) {
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
        $sql = "SELECT FC_Stock_Prices.ID, FC_Stock_Prices.Stock_ID, FC_Stock_Prices.Trade_Date, High, Low, Close, Volume "
                . " FROM FC_WIKI_Codes \n"
                . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID \n"
                . " WHERE Trade_Date >= date_sub(NOW(), INTERVAL 380 DAY) AND FC_WIKI_Codes.Code = '$stock_symbol'"
                . " ORDER BY Trade_date";
        $result = $db->select($sql);

        //LOOP through the prices until the day before the last
        for ($i = 1; $i <= count($result) - 2; $i++) {

            //if the price is higher then follow up then buy 
            if ($follow_up == 1 && $result[$i]['High'] > $purchace_price && $bought == 0) {
                //AND MF is between 50 and 80
                if ($i > $this->MFLength) {
                    $mf = $this->CalculateMF(\array_slice($result, $i - $this->MFLength - 1, $this->MFLength));
                    if ($mf) {
                        //print "Bought - {$result[$i]['Trade_Date']} ";
                        $bought = 1;
                        $STOP_price = $last_low;
                        $purchace_date = $result[$i]['Trade_Date'];
                        if ($result[$i]['Low'] > $purchace_price) {$purchace_price = $result[$i]['Low'];}
                    }else{
                        //MF INDEX OUT OF BOUNDS
                        //$follow_up = 0;
                    }
                }
            }

            //if the price is lower then STOP point then sell
            if ($follow_up == 1 && $result[$i]['Low'] < $STOP_price && $bought == 1) {
                $tmp_gain = $STOP_price - $purchace_price;
                //print "Sold - {$result[$i]['Trade_Date']}, stop - $STOP_price, purchse - $purchace_price, gain - $tmp_gain <br>";
                $follow_up = 0;
                $bought = 0;
                $gain = $gain + $STOP_price - $purchace_price;
                $sql = "INSERT INTO `FC_Results`(`Stock_ID`, `Purchase_Date`, `Purchase_Price`, `Sell_Date`, `Sell_Price`)"
                        . " VALUES ({$result[$i]['Stock_ID']}, '$purchace_date', $purchace_price, '{$result[$i]['Trade_Date']}', $STOP_price)";
                $db->query($sql);
            }

            //Local LOW
            if ($result[$i - 1]['Low'] > $result[$i]['Low'] && $result[$i + 1]['Low'] > $result[$i]['Low']) {
                //SECOND LOW
                if ($last_low > $result[$i]['Low']) {
                    //print "second low - {$result[$i]['Trade_Date']}";
                    $second_low = $last_low;
                    $follow_up = 0;
                    $last_high = 999999;
                    //$sql = "INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 2, fc_stockid)";
                }
                //FIRST LOW
                else {
                    //If Follow up point was found calculate STOP point
                    if ($follow_up == 1) {
                        $STOP_price = $result[$i]['Low'];
                    }
                    //$sql = "INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 1, fc_stockid)";
                }
                //update last low
                $last_low = $result[$i]['Low'];
                //print "first low - {$result[$i]['Trade_Date']}";
            }

            //Local HIGH
            if ($result[$i - 1]['High'] < $result[$i]['High'] && $result[$i + 1]['High'] < $result[$i]['High'] && $result[$i]['High'] > $second_low && $bought == 0) {
                //FOLLOW UP POINT
                if ($last_high < $result[$i]['High']) {
                    //print "follow up - {$result[$i]['Trade_Date']} ";
                    $last_high = 999999;
                    $follow_up = 1;
                    $bought = 0;
                    $purchace_price = $result[$i]['High'];
                }
                //FIRST HIGH
                else {
                    $last_high = $result[$i]['High'];
                    //print "first high - {$result[$i]['Trade_Date']} \n";
                }
            }
        }

        //print "$stock_symbol: Gain - $gain <br>";
        return $gain;
    }

    // MONEY FLOW ALGORITHEM
    //* 1. Typical Price = (High + Low + Close)/3
    //* 2. Raw Money Flow = Typical Price x Volume
    //* 3. Money Flow Ratio = (14-period Positive Money Flow)/(14-period Negative Money Flow)
    //* 4. Money Flow Index = 100 - 100/(1 + Money Flow Ratio)
    private function CalculateMF($data) {
        //SET vars
        $sum_positive = 0;
        $sum_negative = 0;

        //calculate sums of the 14 days
        for ($i = 1; $i <= count($data) - 1; $i++) {
            //calculate this day and the day before "typical price"
            $typical_price_previous = ($data[$i - 1]['High'] + $data[$i - 1]['Low'] + $data[$i - 1]['Close']) / 3;
            $typical_price = ($data[$i]['High'] + $data[$i]['Low'] + $data[$i]['Close']) / 3;

            // if "typical price" > 0 then 1 day positive money flow
            if ($typical_price_previous < $typical_price) {
                $sum_positive = $sum_positive + ($typical_price * $data[$i]['Volume']);
            } else {
                $sum_negative = $sum_negative + ($typical_price * $data[$i]['Volume']);
            }
        }

        //calculate sums of the 14 days
        //$sum_positive = array_sum(array_column($data, 'pp'));
        //$sum_negative = array_sum(array_column($data, 'pn'));
        //calculate ratio
        $money_flow_ratio = $sum_positive / $sum_negative;
        $money_flow_index = 100 - 100 / (1 + $money_flow_ratio);

        //return
        if ($money_flow_index > 43 && $money_flow_index < 60) {
            return true;
        }
        //else
        return false;
    }
    


}
