#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use Hoiast\AluraDownloader\AluraDownloader;

// . Get username, password and courses URLs from configs.json
$configs = json_decode(file_get_contents('./configs.json'), true);

$username = $configs['username'];
$password = $configs['password'];
$coursesURLs = $configs['coursesURLs'];

// . Instantiate Downloader
$downloader = new AluraDownloader($username, $password);

// . Download courses
$downloader->downloadCourses($coursesURLs);

