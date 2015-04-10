<?php
//http://weebtutorials.com/2012/03/pdo-connection-class-using-singleton-pattern/
//calling static method ( '::' rather than '->')
//$db = dbConn::getConnection();
 
//db connection class using singleton pattern
class dbConn{
 
//variable to hold connection object.
protected static $db;
 
//private construct - class cannot be instatiated externally.
private function __construct() {
 
try {
// assign PDO object to db variable
self::$db = new PDO( 'mysql:host=localhost;dbname=DBNAME', 'USERNAME', 'PASSWORD' );
self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch (PDOException $e) {
//Output error - would normally log this to error file rather than output to user.
echo "Connection Error: " . $e->getMessage();
}
 
}
 
// get connection function. Static method - accessible without instantiation
public static function getConnection() {
 
//Guarantees single instance, if no connection object exists then create one.
if (!self::$db) {
//new connection object.
new dbConn();
}
 
//return connection.
return self::$db;
}
 
}//end class
 
?>