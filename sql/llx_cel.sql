-- ============================================================================
-- Copyright (C) 2012 Mikael Carlavan  <contact@mika-carl.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS `llx_cel`(
	`rowid`			int(11) AUTO_INCREMENT,  
	`ref`			varchar(32) NOT NULL,  
	`key`			varchar(32) NOT NULL, 
	`entity`		int(11) DEFAULT 1 NOT NULL,	  
	`datec`			datetime NOT NULL,  
	`type`			varchar(32) NOT NULL,
	`fk_object`      int(11) DEFAULT 0 NOT NULL,
	`firstname` varchar(255) DEFAULT NULL,
  	`lastname` varchar(255) DEFAULT NULL,
  	`job` varchar(255) DEFAULT NULL,
  	`ipsignatory` int(10) UNSIGNED DEFAULT NULL,
  	`datesignature` date DEFAULT NULL
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;
