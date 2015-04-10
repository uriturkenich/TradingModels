<?php

class GetMetaData {

    public function __construct() {
        
    }

    public function LoadSymbols($db, $jsondata) {


        //convert json object to php associative array
        $data = json_decode($jsondata, true);

        //loop through each data
        for ($i = 0; $i <= count($data['docs']); $i++) {

            //get daily trading data
            $Date = $db->quote($data['docs'][$i]['code']);
            $Open = $db->$data['docs'][$i]['code'];
            $High = $db->$data['docs'][$i]['code'];
            $Low = $db->$data['docs'][$i]['code'];
            $Close = $db->$data['docs'][$i]['code'];
            $Volume = $db->$data['docs'][$i]['code'];
            $Ex_Dividend = $db->$data['docs'][$i]['code'];
            $Split_Ratio = $db->$data['docs'][$i]['code'];
            $Adj_Open = $db->$data['docs'][$i]['code'];
            $Adj_High = $db->$data['docs'][$i]['code'];
            $Adj_Low = $db->$data['docs'][$i]['code'];
            $Adj_Close = $db->$data['docs'][$i]['code'];
            $Adj_Volume = $db->$data['docs'][$i]['code'];

            //insert into mysql table

            $sql = "INSERT INTO `FC_WIKI_Codes`(`Date`, `Open`, `High`, `Low`, `Close`, 'Volume', 'Ex-Dividend', 'Split_Ratio', "
                    . "'Adj_Open', 'Adj_High', 'Adj_Low', 'Adj_Close', 'Adj_Volume') "
                    . "VALUES "
                    . "($Date, $Open, $High, $Low, $Close, $Volume, $Ex_Dividend)";
            // Insert the values into the database
            $result = $db->query($sql);
            if ($result !== true) {
                echo $result;
            }
            //if (!mysql_query($sql, $con)) {
            //    die('Error : ' . mysql_error());
            //}
        }
    }

}

?>