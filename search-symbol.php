<?php
require_once "DB.php";

use Db;

if($_POST)
{
$q=$_POST['search'];
//$sql_res=mysql_query("select id,name,email from autocomplete where name like '%$q%' or email like '%$q%' order by id LIMIT 5");
$db = new Db();
$sql = "SELECT `ID`,`Code`, Replace(Name, 'Prices, Dividends, Splits and Trading Volume', '') AS `Name`  FROM `FC_WIKI_Codes` WHERE `Code` LIKE '%$q%' OR Name LIKE '%$q%' ORDER BY `Code` LIMIT 15";
$result = $db->select($sql);
foreach ($result as $row) {
?>
<div class="show" align="left" style="background-color:white;color:black">
    <span class="name"><b><?php echo $row['Code']; ?></b></span>&nbsp; &nbsp;<?php echo $row['Name']; ?><br/>
</div>
<?php
}
}
?>
