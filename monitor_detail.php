<?php

require_once("common.php");
require_once("DB.php");

$feature = preg_replace("/[^a-zA-Z0-9_|]+/", "", htmlspecialchars($_GET['feature'])) ;
$label = $feature;

$db = DB::connect($dsn, true);
if (DB::isError($db)) {
  die ($db->getMessage());
}

$sql = <<<SQL
SELECT `name`, `label`
FROM `features`
WHERE `show_in_lists`=1 AND `name`='{$feature}'
SQL;

$recordset = $db->query($sql);

if (DB::isError($recordset)) {
    die ($recordset->getMessage());
}

// $row[0] = `name`, $row[1] = `label`
while ($row = $recordset->fetchRow()){
   $label = $row[1];
   if (empty($label)) {
       //`label` is NULL, so use `name`, instead.
       $label = $row[0];
   }
}

$recordset->free();
$db->disconnect();
$label = str_replace('|', ' or ', $label);

// Print View.
print_header();
print <<<HTML
<h1>{$label} Usage</h1>

<hr/>
<p class="a_centre">Data is taken every {$collection_interval} minutes. It shows usage for past day, past week, past month and past year.</p>

<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
    // Load the Visualization API and the piechart package.
    google.charts.load('current', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        drawChart_day();
        drawChart_week();
        drawChart_month();
        drawChart_year();
    }

    function drawChart_day() {
        var jsonData = $.ajax({
            url: "graph_data.php?feature={$feature}&days=1",
            dataType: "json",
            async: false
        }).responseText;

        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('chart_div_day'));
        chart.draw(data, {width: 1000, height: 400});
    }

    function drawChart_week() {
        var jsonData = $.ajax({
            url: "graph_data.php?feature={$feature}&days=7",
            dataType: "json",
            async: false
        }).responseText;

        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('chart_div_week'));
        chart.draw(data, {width: 1000, height: 400});
    }

    function drawChart_month() {
        var jsonData = $.ajax({
            url: "graph_data.php?feature={$feature}&days=30",
            dataType: "json",
            async: false
        }).responseText;

        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('chart_div_month'));
        chart.draw(data, {width: 1000, height: 400});
    }

    function drawChart_year() {
        var jsonData = $.ajax({
            url: "graph_data.php?feature={$feature}&days=365",
            dataType: "json",
            async: false
        }).responseText;

        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('chart_div_year'));
        chart.draw(data, {width: 1000, height: 400});
    }
</script>

<h2>Today</h2>
<div id="chart_div_day"></div>
<h2>Past Week</h2>
<div id="chart_div_week"></div>
<h2>Past Month</h2>
<div id="chart_div_month"></div>
<h2>Past Year</h2>
<div id="chart_div_year"></div>
HTML;

print_footer();
?>
