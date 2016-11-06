#!/usr/bin/php
<?php

/**
 * File       convert.php
 * Created    7/31/14 6:16 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v2 or later
 */

$path = null;
$parser = new parser;

if ($argc < 5 || in_array($argv[1], array('--help', '-help', '-h', '-?')))
{

	echo 'This is a command line PHP script with one option.

	  Usage:
	  ' . $argv[0] . ' -p \'midi file to ms/unconverted reference\' -s 1500

	  -p [path] : The path the directory containing the reference files to convert.
	  -s [value] : The value of the start time (e.g. 1500) in milliseconds.

	  With the --help, -help, -h, or -? options, you can get this help.';

	die();
}

foreach ($argv as $key => $arg)
{
	switch ($arg)
	{
		case('-p'):
			$path = $argv[$key + 1];
			break;

		case('-s'):
			$start = $argv[$key + 1];
			break;
	}
}

if ($path)
{
	// From http://php.net/manual/en/function.scandir.php#107215
	$filenames = array_diff(scandir($path), array('..', '.'));

	foreach ($filenames as $filename)
	{
		$file  = $path . '/' . $filename;
		$lines = $parser->parseFile(
			file_get_contents($file)
		);

		$times = $parser->filterTimes($lines);

		file_put_contents(
			$filename,
			$parser->calculateTimes($times, $start)
		);
	}
}

/**
 * Class parser
 */
class parser
{

	function parseFile($file)
	{
		return explode(';', $file);
	}

	function filterTimes($lines)
	{

		foreach ($lines as $line)
		{
			$times = explode(' ', $line);

			if ($times[0] != 0)
			{
				$return[] = $times[0];
			}
		}

		return $return;
	}

	function calculateTimes($times, $start)
	{
		$return = array();

		foreach ($times as $key => $time)
		{
			if ($key == 0)
			{
				$return[$key] = $time + $start . "\n";
			}

			if ($key != 0)
			{
				$return[$key] = $time + $return[$key - 1] . "\n";
			}
		}

		// Add start time to beginning
		array_unshift($return, $start . "\n");

		return $return;
	}

}
