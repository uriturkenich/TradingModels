<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Trading Models</title>
        <link rel="stylesheet" href="../amcharts/style.css" type="text/css">
        <link href="static/css/site.94606a8fe4a6.css" rel="stylesheet" type="text/css" />
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
        
        <?php
        require_once "DarvasBox.php";

        // Get Stock prices
        $d_box = new DarvasBox();
        $d_box->CreateBox($stock_symbol);
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
        <script type="text/javascript" src="jquery-1.8.0.min.js"></script>
        <script type="text/javascript">
            $(function () {
                $(".search").keyup(function ()
                {
                    var searchid = $(this).val();
                    var dataString = 'search=' + searchid;
                    if (searchid != '')
                    {
                        $.ajax({
                            type: "POST",
                            url: "search-symbol.php",
                            data: dataString,
                            cache: false,
                            success: function (html)
                            {
                                $("#result").html(html).show();
                            }
                        });
                    }
                    return false;
                });

                jQuery("#result").live("click", function (e) {
                    var $clicked = $(e.target);
                    var $name = $clicked.find('.name').html();
                    var decoded = $("<div/>").html($name).text();
                    if (decoded === "") {
                        decoded = $("<div/>").html(e.target.innerHTML).text();
                    }
                    $('#searchid').val(decoded);
                });
                jQuery(document).live("click", function (e) {
                    var $clicked = $(e.target);
                    if (!$clicked.hasClass("search")) {
                        jQuery("#result").fadeOut();
                    }
                    if ($clicked.hasClass("LaunchChart")) {
                        window.location.href = "Chart.php?stock-symbol=" + $('#searchid').val();
                    }
                });
                $('#searchid').click(function () {
                    jQuery("#result").fadeIn();
                });
            });
        </script>



        <div id="chartdiv" style="width:100%; height:600px;"></div> 
        <div class="index-learnmore-header" style="background-image:url('')">
            <div class="symbol-input">
                <input type="text" class="search" id="searchid" placeholder="eg. &ldquo;AAPL, D&rdquo;" data-default-symbol="AAPL, D" />
                <span class="LaunchChart">Relaunch Chart</span>
            </div>
            <div id="result"  style="  position:relative;left:0px;top:-45px;width:392px;height:47px;margin:45px auto 0 "></div>
        </div>


            <!--input type="button" id="addPanelButton" onclick="addPanel()" value="add volume panel">
            <input type="button" disabled="true" id="removePanelButton" onclick="removePanel()" value="remove volume panel"-->

            
    </body>

</html>