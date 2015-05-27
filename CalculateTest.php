<?php

require_once "TestModel.php";
// Run test model on all the stocks

$test = new TestModel();
$test->Main();
$test->PrintResults();

?>