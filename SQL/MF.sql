DELIMITER $$

USE `FC`$$

DROP PROCEDURE IF EXISTS `update_MF`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_MF`() NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER 

BEGIN

-- INSERT MISSING DATES
INSERT INTO `FC_MF`(`Stock_ID`, `Stock_Prices_ID`, `LastDate`) 
SELECT Stock_ID, ID, (SELECT MAX(trade_date) FROM FC_Stock_Prices TDATE WHERE TDATE.Stock_ID = TM.Stock_ID AND  TDATE.Trade_date < TM.Trade_Date) AS LastDate
FROM `FC_Stock_Prices` TM 
WHERE ID NOT IN (SELECT Stock_Prices_ID FROM FC_MF);

-- UPDATE missing Last ID
UPDATE FC_MF
INNER JOIN FC_Stock_Prices ON FC_MF.Stock_ID = FC_Stock_Prices.Stock_ID AND FC_MF.LastDate = FC_Stock_Prices.Trade_Date
SET FC_MF.LastDate_ID = FC_Stock_Prices.ID
WHERE FC_MF.LastDate_ID = 0;

-- UPDATE LastDayPeriod
UPDATE `FC_Stock_Prices` TM
INNER JOIN FC_MF ON TM.ID = FC_MF.Stock_Prices_ID
INNER JOIN FC_Stock_Prices TM_LAST ON TM_LAST.ID = FC_MF.LastDate_ID
#1 day period positive
SET FC_MF.1PeriodPositive = IF((TM.High+TM.Low+TM.Close)/3 > (TM_LAST.High+TM_LAST.Low+TM_LAST.Close)/3 , (TM.High+TM.Low+TM.Close)/3,0),
#1 day period negative
FC_MF.1PeriodNegative = IF((TM.High+TM.Low+TM.Close)/3 < (TM_LAST.High+TM_LAST.Low+TM_LAST.Close)/3 , (TM.High+TM.Low+TM.Close)/3,0)
WHERE FC_MF.1PeriodPositive = 0 AND FC_MF.1PeriodNegative = 0;

-- UPDATE MF Index
UPDATE `FC_MF` TM
JOIN (SELECT TM.ID, `LastDate`, `1PeriodPositive`, `1PeriodNegative`, 
#Last 14 days positive
(SELECT SUM(L14.1PeriodPositive) FROM FC_MF L14 WHERE DATEDIFF(TM.LastDate ,L14.LastDate) >= 0 AND 
DATEDIFF(TM.LastDate ,L14.LastDate) < 
CASE DAYNAME(TM.LastDate) WHEN 'Thursday' THEN 20 WHEN 'Wednesday' THEN 20 ELSE 18 END) AS L14P, 
#Last 14 days negative
(SELECT SUM(L14.1PeriodNegative) FROM FC_MF L14 WHERE DATEDIFF(TM.LastDate ,L14.LastDate) >= 0 AND 
DATEDIFF(TM.LastDate ,L14.LastDate) < 
CASE DAYNAME(TM.LastDate) WHEN 'Thursday' THEN 20 WHEN 'Wednesday' THEN 20 ELSE 18 END) AS L14N,
#MF index
100 - 100 / (1 + (SELECT SUM(L14.1PeriodPositive) FROM FC_MF L14 WHERE DATEDIFF(TM.LastDate ,L14.LastDate) >= 0 AND 
DATEDIFF(TM.LastDate ,L14.LastDate) < 
CASE DAYNAME(TM.LastDate) WHEN 'Thursday' THEN 20 WHEN 'Wednesday' THEN 20 ELSE 18 END) /
(SELECT SUM(L14.1PeriodNegative) FROM FC_MF L14 WHERE DATEDIFF(TM.LastDate ,L14.LastDate) >= 0 AND 
DATEDIFF(TM.LastDate ,L14.LastDate) < 
CASE DAYNAME(TM.LastDate) WHEN 'Thursday' THEN 20 WHEN 'Wednesday' THEN 20 ELSE 18 END)) AS MFIndex  
FROM `FC_MF` TM) MFI
ON MFI.ID = TM.ID
SET TM.14PeriodPositive = MFI.L14P, TM.14PeriodNegative = MFI.L14N, TM.MFIndex = MFI.MFIndex
WHERE TM.MFIndex = 0;

END$$


DELIMITER ;