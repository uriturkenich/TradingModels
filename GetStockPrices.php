<?php

require_once "DB.php";
require_once "Quandl.php";

use Db;

class GetStockPrices {

    public function __construct() {
        
    }

    public function GetData($stock_symbol) {

        //Check if the database is up tp date
        $this->CheckData($stock_symbol);
        
        
        $db = new dB();

        //Check when was the last update
        $sql = "SELECT FC_Stock_Prices.* , ResistancePoint FROM `FC_Stock_Prices` \n"
    . " INNER JOIN `FC_WIKI_Codes`\n"
    . " ON FC_Stock_Prices.Stock_ID=FC_WIKI_Codes.ID\n"
    . " LEFT JOIN FC_Darvas ON FC_Darvas.Stock_Prices_ID = FC_Stock_Prices.ID"
    . " WHERE FC_WIKI_Codes.Code = '$stock_symbol' \n"
    . " AND Trade_Date >= ADDDATE(NOW(), -1000)"
    . " ORDER BY `Trade_Date` ";
        $result = $db->select($sql);
        return $result;
    }

    public function CheckData($stock_symbol) {

        // Set database object
        //$db = dbConn::getConnection();
        $db = new dB();

        //Check when was the last update
        $sql = "SELECT ID, FC_Last_Updated FROM `FC_WIKI_Codes` WHERE Code = '$stock_symbol'";
        $result = $db->select($sql);
        $last_updated = $result[0][FC_Last_Updated];
        $stock_id = $result[0][ID];

        // If the data is not updated
        if ($last_updated <> date("Y-m-d")) {
            //get data from Quandl
            $quandl = new Quandl(null, "json");
            print $quandl->last_url;

            //if there is no data 
            if ($last_updated == "0000-00-00") {
                $sData = $quandl->getSymbol("WIKI/$stock_symbol");
            } else {
                //If there is partial data
                if (date("Y-m-d") > date($last_updated)) {
                    // If not then get data from Quandl
                    $sData = $quandl->getSymbol("WIKI/$stock_symbol", [
                        "trim_start" => "$last_updated",
                        "trim_end" => "today",
                    ]);
                }
            }
            
            // INSERT missing data to DB
            $this->InsertToDB($sData, $stock_symbol, $stock_id);

            //UPDATE database field Last_Updated
            $sql = "UPDATE `FC_WIKI_Codes` SET `FC_Last_Updated`= NOW() WHERE Code = '$stock_symbol'";
            $result = $db->query($sql);
            if ($result !== true) {
                echo $result;
            }
        }
    }

    //INSERT missing data from Quandl to DB
    public function InsertToDB($sData, $stock_symbol, $stock_id) {

        $db = new Db(); // Set database object
        //read the json file contents
        $jsondata = $sData;

        //convert json object to php associative array
        $data = json_decode($jsondata, true);
        $i = 0;
        for ($i = 0; $i <= count($data['data']) - 1; $i++) {

            //get the employee details
            $Trade_Date = $db->quote($data['data'][$i][0]);
            $Open = $data['data'][$i][1];
            $High = $data['data'][$i][2];
            $Low = $data['data'][$i][3];
            $Close = $data['data'][$i][4];
            $Volume = $data['data'][$i][5];
            $Ex_Dividend = $data['data'][$i][6];
            $Split_Ratio = $data['data'][$i][7];
            $Adj_Open = $data['data'][$i][8];
            $Adj_Low = $data['data'][$i][9];
            $Adj_Close = $data['data'][$i][10];
            $Adj_Volume = $data['data'][$i][11];

            //insert into mysql table
            $sql = "INSERT INTO `FC_Stock_Prices`(`Stock_ID`, `Trade_Date`, `Open`, `High`, `Low`, `Close`, `Volume`, "
                    . "`Ex_Dividend`, `Split_Ratio`, `Adj_Open`, `Adj_Low`, `Adj_Close`, `Adj_Volume`)"
                    . "VALUES"
                    . "($stock_id,$Trade_Date,$Open,$High,$Low,$Close,$Volume,$Ex_Dividend,$Split_Ratio,$Adj_Open,$Adj_Low,"
                    . "$Adj_Close,$Adj_Volume)";

            // Insert the values into the database
            $result = $db->query($sql);
            if ($result !== true) {
                echo $result;
            }
        }
    }

}
?>

