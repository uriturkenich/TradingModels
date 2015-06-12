<?php

require_once "TestModel.php";
// Run test model on all the stocks

$test = new TestModel();
$test->Main();
$test->CalculateProfit();
//$test->PrintResults();

$test->CalculateProfit2();

?>