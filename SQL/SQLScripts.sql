CREATE TABLE `FC_Stock_Prices` (
 `ID` int(11) NOT NULL AUTO_INCREMENT,
 `Stock_ID` int NOT NULL,
 `Trade_Date` date NOT NULL,
 `Open` float NOT NULL,
 `High` float NOT NULL,
 `Low` float NOT NULL,
 `Close` float NOT NULL,
 `Volume` float NOT NULL,
 `Ex_Dividend` float NOT NULL,
 `Split_Ratio` float NOT NULL,
 `Adj_Open` float NOT NULL,
 `Adj_Low` float NOT NULL,
 `Adj_Close` float NOT NULL,
 `Adj_Volume` float NOT NULL,
 PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1

INSERT INTO `FC`.`FC_Stock_Prices` 
(`ID`, `Date`, `Open`, `High`, `Low`, `Close`, `Volume`, `Ex_Dividend`, `Split_Ratio`, 
`Adj_Open`, `Adj_Low`, `Adj_Close`, `Adj_Volume`) 
VALUES 
(NULL, '2015-03-02', '1', '', '', '', '', '', '', '', '', '', '');


CREATE TABLE `FC_WIKI_Codes` (
 `ID` int(11) NOT NULL AUTO_INCREMENT,
 `Code` text NOT NULL,
 `Name` text NOT NULL,
 `Start_Date` date NOT NULL,
 `End_Date` date NOT NULL,
 `Frequency` text NOT NULL,
 `Last_Updated` date NOT NULL,
 PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5391 DEFAULT CHARSET=latin1


SELECT MAX(Trade_Date) FROM `FC_Stock_Prices` WHERE Stock_ID = 1

SELECT Trade_date
FROM FC_Stock_Prices
LEFT JOIN FC_WIKI_Codes
ON FC_Stock_Prices.Stock_ID=FC_WIKI_Codes.ID
WHERE FC_WIKI_Codes.Code = 'AAPL'

--- MAIN Query for Chart
SELECT TM.* , ResistancePoint,
(SELECT AVG((t2.High+t2.Low+t2.Close)/3) FROM FC_Stock_Prices AS t2
WHERE t2.Trade_Date BETWEEN date_sub(TM.Trade_Date, INTERVAL 50 DAY) AND TM.Trade_Date) AS MovAvg 
FROM `FC_Stock_Prices` TM
INNER JOIN `FC_WIKI_Codes`  ON TM.Stock_ID=FC_WIKI_Codes.ID  
LEFT JOIN FC_Darvas ON FC_Darvas.Stock_Prices_ID = TM.ID 
WHERE FC_WIKI_Codes.Code = 'AAPL'   AND Trade_Date >= ADDDATE(NOW(), -1000) 
ORDER BY `Trade_Date`


--Moving avarage 50
SELECT TM.Trade_Date, (TM.High+TM.Low+TM.Close)/3 AS TypicalPrice,
           (SELECT AVG((t2.High+t2.Low+t2.Close)/3) FROM FC_Stock_Prices AS t2
            WHERE t2.Trade_Date BETWEEN date_sub(TM.Trade_Date, INTERVAL 50 DAY) AND TM.Trade_Date) AS MovAvg
FROM FC_Stock_Prices AS TM

--UPDATE Moving avarage 50
UPDATE `FC_Stock_Prices` 
JOIN (SELECT TM.ID, (SELECT AVG((t2.High+t2.Low+t2.Close)/3) FROM FC_Stock_Prices AS t2
            WHERE TM.Stock_ID = t2.Stock_ID AND t2.Trade_Date BETWEEN date_sub(TM.Trade_Date, INTERVAL 50 DAY) AND TM.Trade_Date) AS MA50
FROM FC_Stock_Prices AS TM) MovAvg ON MovAvg.ID = FC_Stock_Prices.ID
SET FC_Stock_Prices.FC_MA50 = MovAvg.MA50
WHERE FC_Stock_Prices.FC_MA50 = 0