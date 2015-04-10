<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Go Flow!</title>
        <link rel="stylesheet" href="../amcharts/style.css" type="text/css">
        <script src="amcharts/amcharts.js" type="text/javascript"></script>
        <script src="amcharts/serial.js" type="text/javascript"></script>
        <script src="amcharts/amstock.js" type="text/javascript"></script>
        <script src="GenerateChartData.js" type="text/javascript"></script>
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>

        <?php
        require_once "GetStockPrices.php";
       
        // Set variables
        $api_key = "eBkx1Ed14Nkz8shyEJ9g";
        $stock_symbol = $_GET['stock-symbol'];
        $symbol = "WIKI/$stock_symbol";
         
        // Get Stock prices
        $sp = new GetStockPrices();
        $sData = $sp->GetData($stock_symbol);
        
        ?>

    </head>
    <body style="background-color:#FFFFFF">

        <script type="text/javascript">
            AmCharts.ready(function () {
                var chartData = [<?php echo json_encode($sData) ?>];
                generateChartData(chartData);
                createStockChart();
                document.getElementsByClassName('amChartsInputField amcharts-start-date-input')[0].value = "01-01-2015";
                
            });

        </script>


    
    <div id="chartdiv" style="width:100%; height:600px;"></div>
    
    <input type="button" id="addPanelButton" onclick="addPanel()" value="add volume panel">
    <input type="button" disabled="true" id="removePanelButton" onclick="removePanel()" value="remove volume panel">
    
    <br><br>
    Note: 
    <br>This model provides "Follow up" points. They indicate placing a buying order at this price. The buying order can be executed if the price crosses the "follow up" point in the next few days.
    <br> The model basics are - Price over Moving Average 50 + Money flow index is positive + two local lows followed by two local highs.
    <br> Time resolution is daily.
    <br> All data is downloaded from - https://www.quandl.com/
</body>

</html>