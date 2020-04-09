<?php
require_once __DIR__ . "/common.php";
require_once "HTML/Table.php";
require_once 'DB.php';

// First let's get license usage for the product specified in $feature
// Connect to the database.  Use persistent connections.
$db = DB::connect($dsn, true);
if (DB::isError($db)) {
	die ($db->getMessage());
}

$sql = <<<SQL
SELECT DISTINCT `feature`
FROM `events`
WHERE `type`='OUT';
SQL;

$recordset = $db->query($sql);

if (DB::isError($recordset)) {
	die ($recordset->getMessage());
}

// Color code the features so it is easier to group them
// Get a list of different colors
$color = explode(",", $colors);
for ($i = 0; $row = $recordset->fetchRow(); $i++) {
    $features_color[$row[0]] = $color[$i];
}

// $i = 0;
// while ($row = $recordset->fetchRow()) {
//     $features_color["$row[0]"] = $color[$i];
//     $i++;
// }

$sql = <<<SQL
SELECT `date`, `user`, MAX(`feature`), count(*)
FROM `events`
WHERE `type`='OUT'
GROUP BY `date`, `user`

SQL;

//Check what we want to sort data on
if ($_GET['sortby'] == "date") {
	$sql .= "ORDER BY `date`, `user`, `feature` DESC;";
} else if ($_GET['sortby'] == "user" ) {
	$sql .= "ORDER BY `user`, `date`, `feature` DESC;";
} else {
	$sql .= "ORDER BY `feature`, `date`, `user` DESC;";
}

if (isset($debug) && $debug == true)
    print_sql($sql);

$recordset = $db->query($sql);

if (DB::isError($recordset)) {
    die ($recordset->getMessage());
}

// Create a new table object
$tableStyle = "border='1' cellpadding='1' cellspacing='2'";
$table = new HTML_Table($tableStyle);

$table->setColAttributes(1, "align='right'");

// Define a table header
$headerStyle = "style='background: yellow;'";
$colHeaders = array("Date", "User", "Feature", "Total number of checkouts");
$table->addRow($colHeaders, $headerStyle, "TH");

$table->updateColAttributes(3, "align='enter'");

// Right align the 3 column
$table->updateColAttributes(2, "align='right'");

// Add data rows to table
while ($row = $recordset->fetchRow()) {
    $table->AddRow($row, "style='background: {$features_color[$row[2]]};'");
}

$recordset->free();
$db->disconnect();

// View
print_header();

print <<< HTML
<h1>License Checkouts</h1>
<form>
<p>Sort by
<select onChange='this.form.submit();' name="sortby">
	<option value="date">Date</option>
	<option value="user">User</option>
	<option value="feature">Feature</option>
</select>
</p>
</form>
HTML;

$table->display();
print_footer();
?>
