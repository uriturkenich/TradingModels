DELIMITER $$

USE `FC`$$

DROP PROCEDURE IF EXISTS `main_darvas_box`$$

CREATE  PROCEDURE `main_darvas_box`() NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER 

BEGIN
 
DECLARE v_finished INTEGER DEFAULT 0;
DECLARE t_id FLOAT DEFAULT 0;

 
 -- declare cursor for price table
 DECLARE stockid_cursor CURSOR FOR 
 SELECT Stock_ID FROM FC_Stock_Prices GROUP BY Stock_ID;

 
 -- declare NOT FOUND handler
DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET v_finished = 1;
 
OPEN stockid_cursor;
 
build_boxes: LOOP
 
FETCH stockid_cursor INTO t_id;
 
IF v_finished = 1 THEN 
LEAVE build_boxes;
END IF;

CALL `build_darvas_box`(t_id);

END LOOP build_boxes;
 
CLOSE stockid_cursor;
 
 
 
END$$


DELIMITER ;

