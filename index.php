#!/usr/bin/php
<?php
/**
 * File       index.php
 * Created    1/25/14 4:56 PM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

// Traverse into each directory matching test*
foreach (glob('subject*', GLOB_ONLYDIR) as $dir)
{
	$dir       = basename($dir);
	$i         = 1;
	$isNewFile = false;

	// If data and reference file exists
	while (
		file_exists(dirname(__FILE__) . '/' . $dir . '/data' . $i . '.txt') &&
		file_exists(dirname(__FILE__) . '/' . $dir . '/reference' . $i . '.txt')
	)
	{

		if (!file_exists("data$i.csv"))
		{
			$isNewFile = true;
		}

		// create a file pointer connected to the output stream
		$output = fopen("data$i.csv", 'a');

		// output the column headings
		if ($isNewFile == true)
		{
			fputcsv($output, array(
					'subject',
					'attempt',
					'reference',
					'variance',
					'success')
			);
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
				$row = array(
					$dir,
					$datum,
					$reference,
					$variance,
					'1');
			}
			else
			{
				$row = array(
					$dir,
					$datum);
			}

			fputcsv($output, (array) $row);
		}

		$i++;
	}
}
