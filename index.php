<?php

/**
 * File       index.php
 * Created    1/25/14 10:29 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

$host   = 'localhost';
$dbname = 'gh-analyzer';
$user   = 'root';
$pass   = '';

// Connect to databse
try
{
	# MySQL with PDO_MYSQL
	$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e)
{
	echo $e->getMessage();
}

// Traverse into each directory matching test*
foreach (glob('test*', GLOB_ONLYDIR) as $dir)
{
	$dir = basename($dir);
	$i   = 1;

	// If data and reference file exists
	while (file_exists(dirname(__FILE__) . '/' . $dir . '/data' . $i . '.txt') && file_exists(dirname(__FILE__) . '/' . $dir . '/reference' . $i . '.txt'))
	{
		/**
		 * Create database table for each directory only if
		 * data and reference files exist
		 */
		$query = "CREATE TABLE IF NOT EXISTS `test$i` (
					`id`        INT(11) NOT NULL AUTO_INCREMENT,
					`test`      VARCHAR(255) DEFAULT '',
					`data`      DECIMAL(8,2) DEFAULT 0,
					`reference` DECIMAL(5,0) DEFAULT 0,
					`variance`  DECIMAL(8,2) DEFAULT 0,
					`success`   INT(11) DEFAULT 0,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		try
		{
			$st = $pdo->prepare($query);
			$st->execute();
		} catch (PDOException $e)
		{
			die('damn it!');
			echo $e->getMessage();
		}

		$data       = file_get_contents($dir . '/data' . $i . '.txt');
		$references = file_get_contents($dir . '/reference' . $i . '.txt');
		$data       = explode("\n", $data);
		$references = explode("\n", $references);

		foreach ($references as $reference)
		{
			$hit = null;

			foreach ($data as $datum)
			{
				if (($datum > ($reference - 100)) && ($datum < ($reference + 100)))
				{
					$hit      = true;
					$variance = $datum - $reference;

					echo 'Hit: ' . $datum . '<br/>';
					echo 'Reference: ' . $reference . '<br/>';
					echo 'Variance: ' . $variance . '<br/><br/>';

					$query = "INSERT INTO test$i
					(`test`, `data`, `reference`, `variance`, `success`)
					VALUES ('$dir', '$datum', '$reference', '$variance', '1');";
					try
					{
						$st = $pdo->prepare($query);
						$st->execute();
					} catch (PDOException $e)
					{
						echo $e->getMessage();
					};

					break;
				}
				else
				{
					$query = "INSERT INTO test$i
					(`test`, `data`)
					VALUES ('$dir', '$datum');";
				}
			}

		}

		$i++;
	}
}

