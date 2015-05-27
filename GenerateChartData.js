

var chart;
var chartData = [];
var chartDataMA50 = [];
var events = [];
var newPanel;
var stockPanel;
var graph = new AmCharts.StockGraph();

function generateChartData(arr) {
//JSON Array structure
//0: "Date"
//1: "Open"
//2: "High"
//3: "Low"
//4: "Close"
//5: "Volume"


    //var arr = JSON.parse(chartData);  
    var lastDate = new Date(); //check if a date appears twice in the array

    //Go through the data array and insert to the chart data
    for (var i = 0; i <= arr[0].length - 1; i++) {
        var newDate = new Date(arr[0][i]['Trade_Date']);
        newDate.setHours(0, 0, 0, 0);
        //check if a date appears twice in the array
        if (newDate.toDateString() !== lastDate.toDateString()) {

            lastDate = newDate;
            var open = arr[0][i]['Open'];
            var close = arr[0][i]['Close'];
            var low = arr[0][i]['Low'];
            var high = arr[0][i]['High'];
            var volume = arr[0][i]['Volume'];
            var ma50 = arr[0][i]['FC_MA50'];


            chartData.push({
                date: newDate,
                open: open,
                close: close,
                high: high,
                low: low,
                volume: volume,
                value: ma50
            });
        }
        // If the stock was bought make a note
        if (arr[0][i]['Purchase_Price'] > 0) {
            events.push({
                date: newDate,
                type: "sign",
                backgroundColor: "#85CDE6",
                graph: graph,
                text: "P",
                description: "Purchase order was executed at price - " + arr[0][i]['Purchase_Price']
            });
        }
        // If the stock was sold make a note
        if (arr[0][i]['Sell_Price'] > 0) {
            events.push({
                date: newDate,
                type: "sign",
                backgroundColor: "#85CDE6",
                graph: graph,
                text: "S",
                description: "Sell order was executed at price - " + arr[0][i]['Sell_Price']
            });
        }
    }
}

function createStockChart() {
    chart = new AmCharts.AmStockChart();
    chart.pathToImages = "../amcharts/images/";
    chart.balloon.horizontalPadding = 13;

    // DATASET //////////////////////////////////////////
    var dataSet = new AmCharts.DataSet();
    dataSet.title = "";
    dataSet.fieldMappings = [{
            fromField: "open",
            toField: "open"
        }, {
            fromField: "close",
            toField: "close"
        }, {
            fromField: "high",
            toField: "high"
        }, {
            fromField: "low",
            toField: "low"
        }, {
            fromField: "volume",
            toField: "volume"
        }, {
            fromField: "value",
            toField: "value"
        }];
    dataSet.color = "#7f8da9";
    dataSet.dataProvider = chartData;
    dataSet.categoryField = "date";

    var dataSet2 = new AmCharts.DataSet();
    dataSet2.fieldMappings = [{
            fromField: "value",
            toField: "value"
        }];
    dataSet2.color = "#fac314";
    dataSet2.dataProvider = chartData;
    dataSet2.compared = true;
    dataSet2.title = "MA 50";
    dataSet2.categoryField = "date";

    chart.dataSets = [dataSet, dataSet2];

    // PANELS ///////////////////////////////////////////
    stockPanel = new AmCharts.StockPanel();
    stockPanel.title = "Value";

    // graph of first stock panel
    graph.type = "candlestick";
    graph.openField = "open";
    graph.closeField = "close";
    graph.highField = "high";
    graph.lowField = "low";
    graph.valueField = "close";
    graph.lineColor = "#7f8da9";
    graph.fillColors = "#7f8da9";
    graph.negativeLineColor = "#db4c3c";
    graph.negativeFillColors = "#db4c3c";
    graph.fillAlphas = 1;
    // adds a comparison graph - MA 50
    //graph.comparable = true;
    //graph.compareField = "value";
    graph.balloonText = "open:<b>[[open]]</b><br>close:<b>[[close]]</b><br>low:<b>[[low]]</b><br>high:<b>[[high]]</b>";
    graph.useDataSetColors = false;
    stockPanel.addStockGraph(graph);

    var stockLegend = new AmCharts.StockLegend();
    stockLegend.markerType = "none";
    stockLegend.markerSize = 0;
    stockLegend.valueTextRegular = undefined;
    stockLegend.valueWidth = 250;
    stockPanel.stockLegend = stockLegend;

    chart.panels = [stockPanel];
    chart.categoryAxesSettings.equalSpacing = true;

    //var categoryAxesSettings = new AmCharts.CategoryAxesSettings();
    //categoryAxesSettings.minPeriod = "d";
    //chart.categoryAxesSettings = categoryAxesSettings;
    //categoryAxesSettings.equalSpacing = true;

    //var valueAxis = new AmCharts.ValueAxis(); valueAxis.minMaxMultiplier = 1.05; panel.addValueAxis(valueAxis);



    // OTHER SETTINGS ////////////////////////////////////
    var sbsettings = new AmCharts.ChartScrollbarSettings();
    sbsettings.graph = graph;
    sbsettings.graphType = "line";
    sbsettings.usePeriod = "WW";
    chart.chartScrollbarSettings = sbsettings;

    // Enable pan events
    var panelsSettings = new AmCharts.PanelsSettings();
    panelsSettings.panEventsEnabled = true;
    chart.panelsSettings = panelsSettings;

    // CURSOR
    var cursorSettings = new AmCharts.ChartCursorSettings();
    cursorSettings.valueBalloonsEnabled = true;
    cursorSettings.fullWidth = true;
    cursorSettings.cursorAlpha = 0.1;
    cursorSettings.valueLineEnabled = true;
    chart.chartCursorSettings = cursorSettings;


    // PERIOD SELECTOR ///////////////////////////////////
    var periodSelector = new AmCharts.PeriodSelector();
    periodSelector.position = "bottom";
    periodSelector.periods = [{
            period: "DD",
            count: 10,
            label: "10 days"
        }, {
            period: "MM",
            selected: true,
            count: 1,
            label: "1 month"
        }, {
            period: "YYYY",
            count: 1,
            label: "1 year"
        }, {
            period: "YTD",
            label: "YTD"
        }, {
            period: "MAX",
            label: "MAX"
        }];
    chart.periodSelector = periodSelector;
    dataSet.stockEvents = events;

    chart.write('chartdiv');
}

// add volume panel
function addPanel() {
    newPanel = new AmCharts.StockPanel();
    newPanel.allowTurningOff = true;
    newPanel.title = "Volume";
    newPanel.showCategoryAxis = false;

    var graph1 = new AmCharts.StockGraph();
    graph1.valueField = "volume";
    graph1.type = "column";
    graph1.fillAlphas = 0.15;
    newPanel.addStockGraph(graph1);

    var legend = new AmCharts.StockLegend();
    legend.markerType = "none";
    legend.markerSize = 0;
    newPanel.stockLegend = legend;

    chart.addPanelAt(newPanel, 1);
    chart.validateNow();

    document.getElementById("addPanelButton").disabled = true;
    document.getElementById("removePanelButton").disabled = false;
}

//remove volume panel
function removePanel() {
    chart.removePanel(newPanel);
    chart.validateNow();

    document.getElementById("addPanelButton").disabled = false;
    document.getElementById("removePanelButton").disabled = true;
}

