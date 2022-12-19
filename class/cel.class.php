<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr> http://www.mikael-carlavan.fr
 * Copyright (C) 2022 Julien Marchand <julien.marchand@iouston.com>
 *                                                
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/cel/class/cel.class.php
 *      \ingroup    cel
 *      \brief      File of class to manage trips and working credit notes
 */

require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/comm/propal/class/propal.class.php");

class CEL extends CommonObject
{
	var $db;
	var $error;
	
	var $element = 'cel';
	var $table_element = 'cel';
	var $table_element_line = '';
	var $fk_element = 'fk_cel';
	var $ismultientitymanaged = 0;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	

	var $id;
	var $ref;
	var $key;
	var $entity;
	var $datec;		
	var $type = 'propal';
	var $fk_object;	
	
	
   /**
	*  \brief  Constructeur de la classe
	*  @param  DB          handler acces base de donnees
	*/
	function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Fetch object from database
	 *
	 * @param 	id	    Id of the payment
     * @param 	key  	Key of the payment
     * @param   all 	0 = return just id, 1 return all fields
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function fetch($id, $key = '', $all=0)
	{
	    global $conf, $langs;

        if (!$id && empty($key))
        {
			return -1;
        }
        
        $sql = "SELECT c.rowid, c.ref, c.key, c.type, c.fk_object, c.datec, c.lastname, c.firstname, c.job, c.ipsignatory, c.datesignature";
        $sql.= " FROM ".MAIN_DB_PREFIX."cel as c";
        $sql.= " WHERE c.entity = ".$conf->entity;
    
        if ($id)   $sql.= " AND c.rowid = ".$id;
        if ($key)  $sql.= " AND c.key = '".$this->db->escape($key)."'";

		dol_syslog("CEL::fetch sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);
		if ($result > 0)
		{
			$num = $this->db->num_rows($result);
			
			if ($num)
			{
				$obj = $this->db->fetch_object($result);

				$this->id                = $obj->rowid;
				$this->ref               = $obj->ref;
				$this->key               = $obj->key;
				$this->entity            = $obj->entity;
				$this->datec             = $this->db->jdate($obj->datec);			
				$this->type    			= trim($obj->type);
				$this->fk_object    	= $obj->fk_object;
				$this->firstname    	= $obj->firstname;
				$this->lastname    		= $obj->lastname;
				$this->job    			= $obj->job;
				$this->ipsignatory    	=  long2ip($obj->ipsignatory);
				$this->datesignature 	= $this->db->jdate($obj->datesignature);

				if($all==0){
					return $this->id;
				}else{
					return $this;
				}
				
            }
            else
            {
            	return 0;
            }
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}
	}



	
	
	/**
	 * Create object in database
	 *
	 * @param 	$user	User that creates
	 * @return 	int		<0 if KO, >0 if OK
	 */
	function create($user)
	{
		global $conf, $langs;


        $this->datec = dol_now();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."cel (";
        $sql.= "`ref`";
        $sql.= ", `entity`";
        $sql.= ", `datec`";
        $sql.= ", `key`";
        $sql.= ", `type`";
		$sql.= ", `fk_object`";
		$sql.= ") ";
        $sql.= " VALUES (";
		$sql.= " ".($this->ref ? "'".$this->db->escape($this->ref)."'" : "''");
		$sql.= ", ".$conf->entity." ";
        $sql.= ", '".$this->db->idate($this->datec)."'";
        $sql.= ", ".($this->key ? "'".$this->db->escape($this->key)."'" : "''");
        $sql.= ", ".($this->type ? "'".$this->db->escape($this->type)."'" : "''");
		$sql.= ", ".($this->fk_object ? $this->fk_object : 0);
		$sql.= ")";

echo $sql;

		dol_syslog("CEL::create sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."cel");
			return $this->id;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}

	}

	/**
	 * update object in database
	 * @param 	$user	User that creates
	 * @return 	int		<0 if KO, >0 if OK
	 */

	function update($user)	
	{
		global $conf, $langs;

        $this->datesignature = dol_now();

		$sql = "UPDATE ".MAIN_DB_PREFIX."cel SET";
        $sql.= " `firstname` = '".$this->db->escape($this->firstname)."'";
		$sql.= ", `lastname` = '".$this->db->escape($this->lastname)."'";
		$sql.= ", `job` = '".$this->db->escape($this->job)."'";
		$sql.= ", `ipsignatory` = '".ip2long($this->ipsignatory)."'";
		$sql.= ", `datesignature` = '".$this->db->idate($this->datesignature)."'";
        $sql.= " WHERE `rowid` = ".$this->id;

		dol_syslog("CEL::update sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}

	}
	
	/*
	function closed($user)	
	{
		global $conf, $langs;

        $this->datev = dol_now();

		$sql = "UPDATE ".MAIN_DB_PREFIX."cel SET";
        $sql.= " `datev` = '".$this->db->idate($this->datec)."'";
		$sql.= ", `closed` = 1";
        $sql.= " WHERE `rowid` = ".$this->id;

		dol_syslog("CEL::closed sql=".$sql, LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			return -1;
		}

	} */
}

?>
