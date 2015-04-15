DELIMITER $$

USE `FC`$$

DROP PROCEDURE IF EXISTS `build_darvas_box`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `build_darvas_box`(fc_stockid INT) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER 

BEGIN
 
DECLARE v_finished INTEGER DEFAULT 0;
DECLARE t_id FLOAT DEFAULT 0;
DECLARE t_trade_date DATE;
DECLARE t_high FLOAT DEFAULT 0;
DECLARE t_low FLOAT DEFAULT 0;

-- 4 consecocative low days
DECLARE low1 FLOAT DEFAULT 0;
DECLARE low2 FLOAT DEFAULT 0;
DECLARE low3 FLOAT DEFAULT 0;
DECLARE low4 FLOAT DEFAULT 0;

-- 4 highs
DECLARE high1 FLOAT DEFAULT 0;
DECLARE high2 FLOAT DEFAULT 0;
DECLARE high3 FLOAT DEFAULT 0;
DECLARE high4 FLOAT DEFAULT 0;

-- indicators for the algo
DECLARE last_trade_date DATE;
DECLARE last_id INT DEFAULT 0;
DECLARE last_low FLOAT DEFAULT 0;
DECLARE last_high FLOAT DEFAULT 99999;
DECLARE follow_point FLOAT DEFAULT 0;
DECLARE second_low FLOAT DEFAULT 0;
 
 -- declare cursor for price table
 DECLARE price_cursor CURSOR FOR 
 SELECT ID, Trade_Date, High, Low FROM FC_Stock_Prices 
 WHERE Trade_Date >= date_sub(NOW(), INTERVAL 1000 DAY) AND Stock_ID = fc_stockid
 ORDER BY Trade_date;

 
 -- declare NOT FOUND handler
DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET v_finished = 1;
 
OPEN price_cursor;


-- delete data from table and recreate it
DELETE FROM FC_Darvas WHERE Stock_ID = fc_stockid;
 
get_price: LOOP
 
FETCH price_cursor INTO t_id, t_trade_date, t_high, t_low;
 
IF v_finished = 1 THEN 
LEAVE get_price;
END IF;



 -- set lows
IF low1 = 0 AND low2 = 0 AND low3 = 0 AND low4 = 0 THEN 
    SET low1 = t_low;
ELSE
    IF low2 = 0 AND low3 = 0 AND low4 = 0 THEN
        SET low2 = low1;
        SET low1 = t_low;
    ELSE
        IF low3 = 0 AND low4 = 0 THEN
            SET low3 = low2;
            SET low2 = low1;
            SET low1 = t_low;
        ELSE 
                SET low4 = low3;
                SET low3 = low2;
                SET low2 = low1;
                SET low1 = t_low;
        END IF;
    END IF;
END IF;

 -- set highs
IF high1 = 0 AND high2 = 0 AND high3 = 0 AND high4 = 0 THEN 
    SET high1 = t_high;
ELSE
    IF high2 = 0 AND high3 = 0 AND high4 = 0 THEN
        SET high2 = high1;
        SET high1 = t_high;
    ELSE
        IF high3 = 0 AND high4 = 0 THEN
            SET high3 = high2;
            SET high2 = high1;
            SET high1 = t_high;
        ELSE 
                SET high4 = high3;
                SET high3 = high2;
                SET high2 = high1;
                SET high1 = t_high;
        END IF;
    END IF;
END IF;

-- select high1,high2,high3;

-- if there's data, find second low
IF low4 > 0 THEN
    IF low2 <= low1 AND low2 <= low3 THEN
        IF last_low > low2 THEN
            SELECT 'second low', last_trade_date, last_low, low2, last_high;
            SET second_low = last_low;
            SET last_high = 99999;
            INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 2, fc_stockid);
        ELSE
            -- SELECT 'local low', last_trade_date, low1,low2,low3;
            SET second_low = 0;
            INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 1, fc_stockid);
            SELECT 'first low', last_trade_date, last_low, low2, last_high;
        END IF;
        SET last_low = low2;
        
        
    END IF;
    IF high2 >= high1 AND high2 >= high3 AND high2 > second_low THEN
        IF last_high < high2 THEN
            SELECT 'Follow up point!!!', last_trade_date, second_low, high2, last_high;
            SET second_low = 1;
            SET last_high = 99999;
            INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 4, fc_stockid);
        ELSE 
            SET last_high = high2;
            INSERT INTO `FC_Darvas`(`Stock_Prices_ID`, `ResistancePoint`, `Stock_ID`) VALUES (last_id, 3, fc_stockid);  
            SELECT 'first high', last_trade_date, second_low, high1,high2,high3, last_high;
        END IF;
    END IF;
END IF;

SET last_trade_date = t_trade_date;
SET last_id = t_id;

END LOOP get_price;
 
CLOSE price_cursor;
 
 
 
END$$


DELIMITER ;


# TRUNCATE TABLE FC_Darvas;