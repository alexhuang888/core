<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

include "./moduleFunctions.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonFinanceFeeCategoryID=$_POST["gibbonFinanceFeeCategoryID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/feeCategories_manage_delete.php&gibbonFinanceFeeCategoryID=$gibbonFinanceFeeCategoryID" ;
$URLDelete=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/feeCategories_manage.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Finance/feeCategories_manage_delete.php")==FALSE) {
	//Fail 0
	$URL.="&deleteReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	if ($gibbonFinanceFeeCategoryID=="") {
		//Fail1
		$URL.="&deleteReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("gibbonFinanceFeeCategoryID"=>$gibbonFinanceFeeCategoryID); 
			$sql="SELECT * FROM gibbonFinanceFeeCategory WHERE gibbonFinanceFeeCategoryID=:gibbonFinanceFeeCategoryID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL.="&deleteReturn=fail2" ;
			header("Location: {$URL}");
			break ;
		}

		if ($result->rowCount()!=1) {
			//Fail 2
			$URL.="&deleteReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			//Write to database
			try {
				$data=array("gibbonFinanceFeeCategoryID"=>$gibbonFinanceFeeCategoryID); 
				$sql="DELETE FROM gibbonFinanceFeeCategory WHERE gibbonFinanceFeeCategoryID=:gibbonFinanceFeeCategoryID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL.="&deleteReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
			
			//Update any fees using this category to "Other"
			try {
				$data=array("gibbonFinanceFeeCategoryID"=>$gibbonFinanceFeeCategoryID); 
				$sql="UPDATE gibbonFinanceFee SET gibbonFinanceFeeCategoryID=1 WHERE gibbonFinanceFeeCategoryID=:gibbonFinanceFeeCategoryID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { }
			
			try {
				$data=array("gibbonFinanceFeeCategoryID"=>$gibbonFinanceFeeCategoryID); 
				$sql="UPDATE gibbonFinanceInvoiceFee SET gibbonFinanceFeeCategoryID=1 WHERE gibbonFinanceFeeCategoryID=:gibbonFinanceFeeCategoryID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { print "Here" ;}

			//Success 0
			$URLDelete=$URLDelete . "&deleteReturn=success0" ;
			header("Location: {$URLDelete}");
		}
	}
}
?>