<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/nodePassword.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$link = mysql_connect('localhost','root', $nodePassword) or die('MySQL connection failure');

$lastRealms = array('default' => my_get_cwd() . '/shared');
$lastIPs = array();

if(isset($_GET['cleandb'])) {
	$hasDB = mysql_select_db('bfs');
	if($hasDB) {
		$query = 'SELECT * FROM user';
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		$lastUser = mysql_fetch_array($result, MYSQL_ASSOC);
		
		$q = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'bfs' AND  TABLE_NAME = 'realms'";
		$result = mysql_query($q) or die('Query failed: ' . mysql_error());
		if(mysql_num_rows($result) > 0) {
			$q = "SELECT * FROM `bfs`.`realms`";
			$result = mysql_query($q) or die('Query failed: ' . mysql_error());
			while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$lastRealms[$line['name']] = $line['path'];
			}
		}
		$q = "SELECT * FROM `bfs`.`nodes`";
		$result = mysql_query($q) or die('Query failed: ' . mysql_error());
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($line['online'] == 1) $lastIPs[] = $line['ip'];
		}
	}
	$query = 'DROP DATABASE bfs';
	mysql_query($query) or die('Query failed: ' . mysql_error());
}

$hasDB = mysql_select_db('bfs');
if(!$hasDB) {
	$query = 'CREATE DATABASE bfs';
	mysql_query($query) or die('Query failed: ' . mysql_error());
	$hasDB = mysql_select_db('bfs');
}
if(!$hasDB) {
	die("create database failure");
}

$query = <<<END
CREATE TABLE IF NOT EXISTS `user` (
  `id` VARCHAR(10) NOT NULL,
  `ip` text NOT NULL,
  `name` text NOT NULL,
  `time` int(11) NOT NULL,
  `version` text NOT NULL,
  `updated` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `builds` (
  `hash` varchar(40) NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` text NOT NULL,
  `name` text NOT NULL,
  `ip` text NOT NULL,
  `online` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `realms` (
  `name` text NOT NULL,
  `path` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `files` (
  `hash` text NOT NULL,
  `realm` text NOT NULL,
  `fname` text NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `filecache` (
  `hash` text NOT NULL,
  `hosts` text NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

$query = <<<END
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` text NOT NULL,
  `rehash` text NOT NULL,
  `userid` text NOT NULL,
  `body` text NOT NULL,
  `attach` text NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
END;
mysql_query($query) or die('Query failed: ' . mysql_error());

foreach($lastRealms as $k => $v) {
	$q = "INSERT INTO `bfs`.`realms` (`name`, `path`) VALUES ('$k', '$v');";
	mysql_query($q) or die('Query failed: ' . mysql_error());
}

if(isset($_GET['cleandb']) && array_key_exists('id', $lastUser) && $lastUser['id'] != "") {
	$q = "INSERT INTO `bfs`.`user` (`id`, `name`, `ip`, `time`, `version`, `updated`) VALUES ('" . $lastUser['id'] . "', '" . $lastUser['name'] . "', '" . $lastUser['ip'] . "', '0', '" . $version . "', 'false');";
	mysql_query($q) or die('Query failed: ' . mysql_error());
	
	$q = "INSERT INTO `bfs`.`nodes` (`id`, `name`, `ip`) VALUES ('" . $lastUser['id']. "', '" . $lastUser['name'] . "', '" . $lastUser['ip'] ."');";
	mysql_query($q) or die('Query failed: ' . mysql_error());	

	$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('" . $lastUser['ip'] . "');";
	foreach($lastIPs as $ip) {
		$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('$ip');";
		mysql_query($q) or die('Query failed: ' . mysql_error());
	}
	mysql_query($q) or die('Query failed: ' . mysql_error());
}

?>
