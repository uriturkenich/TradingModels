<?php
require_once "DB.php";

use Db;

if($_POST)
{
$q=$_POST['search'];
//$sql_res=mysql_query("select id,name,email from autocomplete where name like '%$q%' or email like '%$q%' order by id LIMIT 5");
$db = new Db();
$sql = "SELECT FC_WIKI_Codes.ID, FC_WIKI_Codes.Code AS `Code`, "
    . " Replace(FC_WIKI_Codes.Name, 'Prices, Dividends, Splits and Trading Volume', '') AS `Name` \n"
    . " FROM FC_WIKI_Codes \n"
    . " INNER JOIN FC_Stock_Prices ON FC_WIKI_Codes.ID = FC_Stock_Prices.Stock_ID \n"
    //. " WHERE FC_WIKI_Codes.Code LIKE '$q%'"
    . " GROUP BY FC_WIKI_Codes.ID, FC_WIKI_Codes.Code, FC_WIKI_Codes.Name"
    . " ORDER BY FC_WIKI_Codes.Code LIMIT 15";
//$sql = "SELECT `ID`,`Code`, Replace(Name, 'Prices, Dividends, Splits and Trading Volume', '') AS `Name`  FROM `FC_WIKI_Codes` WHERE `Code` LIKE '%$q%' OR Name LIKE '%$q%' ORDER BY `Code` LIMIT 15";
    /* @var $result type */
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
