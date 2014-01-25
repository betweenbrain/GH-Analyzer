<?php

/**
 * File       index2.php
 * Created    1/25/14 4:56 PM
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
foreach (glob('subject*', GLOB_ONLYDIR) as $dir)
{
	$dir = basename($dir);
	$i   = 1;

	// If data and reference file exists
	while (file_exists(dirname(__FILE__) . '/' . $dir . '/data' . $i . '.txt') && file_exists(dirname(__FILE__) . '/' . $dir . '/reference' . $i . '.txt'))
	{
		/**
		 * Create database table for each test only if
		 * data and reference files exist
		 */
		$query = "CREATE TABLE IF NOT EXISTS `data$i` (
					`id`        INT(11) NOT NULL AUTO_INCREMENT,
					`subject`      VARCHAR(255) DEFAULT '',
					`attempt`      DECIMAL(8,2) DEFAULT 0,
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

		foreach ($data as $datum)
		{
			$hit = null;

			foreach ($references as $reference)
			{

				if (($datum > ($reference - 100)) && ($datum < ($reference + 100)))
				{
					$hit      = true;
					$variance = $datum - $reference;
					break;
				}
			}

			if ($hit)
			{

				$query = "INSERT INTO data$i
				(`subject`, `attempt`, `reference`, `variance`, `success`)
				VALUES ('$dir', '$datum', '$reference', '$variance', '1');";
			}
			else
			{
				$query = "INSERT INTO data$i
				(`subject`, `attempt`)
				VALUES ('$dir', '$datum');";
			}

			try
			{
				$st = $pdo->prepare($query);
				$st->execute();
			} catch (PDOException $e)
			{
				echo $e->getMessage();
			};

		}

		$i++;
	}
}