<?php

/*
 * NOWHERE Plugin Workspace Framework, adapted for WorldEditArt
 *
 * Copyright (C) 2015-2016 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

use WorldEditArt\Lang\LanguageFileParser;

if(version_compare(PHP_VERSION, "7.0.0", "<")){
	echo "Fatal: This entry script requires PHP >=7.0.0!\n";
	exit;
}

chdir(__DIR__);

$i = 0;
function addDir(Phar $phar, $from, $localDir){
	global $i;
	$from = rtrim(realpath($from), "/") . "/";
	$localDir = rtrim($localDir, "/") . "/";
	if(!is_dir($from)){
		echo "WARNING: $from is not a directory!";
		return;
	}
	/** @var SplFileInfo $file */
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from)) as $file){
		if(!$file->isFile()){
			continue;
		}
		$incl = substr($file, strlen($from));
		$target = $localDir . $incl;
		$phar->addFile($file, $target);
		printf("\r[%d] Added file $target", ++$i);
	}
	echo "\n";
}

function walkPerms(array $stack, array &$perms){
	$prefix = implode(".", $stack) . ".";
	foreach(array_keys($perms) as $key){
		$perms[$prefix . $key] = $perms[$key];
		unset($perms[$key]);
		$stack2 = $stack;
		$stack2[] = $key;
		if(isset($perms[$prefix . $key]["children"])){
			walkPerms($stack2, $perms[$prefix . $key]["children"]);
		}
	}
}

function parsePerms(SimpleXMLElement $element, array $parents){
//	var_dump($element);
	$prefix = "";
	foreach($parents as $parent){
		$prefix .= $parent . ".";
	}
	if(isset($element->attributes()->description)){
		$description = (string) $element->attributes()->description;
	}
	if(isset($element->attributes()->default)){
		$default = (string) $element->attributes()->default;
	}
	$children = [];
	foreach($element->children() as $childName => $child){
		$copy = $parents;
		$copy[] = $childName;
		$children[$prefix . $childName] = parsePerms($child, $copy);
	}
	$ret = [];
	if(count($children) > 0){
		$ret["children"] = $children;
	}
	if(isset($description)){
		$ret["description"] = $description;
	}
	if(isset($default)){
		$ret["default"] = $default;
	}
	return $ret;
}

$info = json_decode(file_get_contents("compile/info.json"));
$NAME = $info->name;

$CLASS = "Dev";
$opts = getopt("", ["rc", "beta", "travis"]);
if(isset($opts["beta"])){
	$CLASS = "Beta";
}elseif(isset($opts["rc"])){
	$CLASS = "RC";
}elseif(isset($opts["travis"])){
	$CLASS = "Travis";
}

$VERSION = $info->version->major . "." . $info->version->minor . "-" . $CLASS . "#" . ($info->nextBuild++);
file_put_contents("compile/info.json", json_encode($info, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$permissions = [];
if(is_file("permissions.xml")){
	$permissions = parsePerms((new SimpleXMLElement(file_get_contents("permissions.xml"))), [])["children"];
}

$file = "compile/" . $NAME . "_" . $CLASS . ".phar";
if(is_file($file)){
	unlink($file);
}
$phar = new Phar($file);
$phar->setStub('<?php require_once "phar://" . __FILE__ . "/entry/entry.php"; __HALT_COMPILER();');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

$phar->addFromString("plugin.yml", yaml_emit([
	"name" => $NAME,
	"author" => $info->author,
	"authors" => isset($info->authors) ? $info->authors : [],
	"main" => $info->main,
	"api" => $info->api,
	"depend" => isset($info->depend) ? $info->depend : [],
	"softdepend" => isset($info->softdepend) ? $info->softdepend : [],
	"loadbefore" => isset($info->loadbefore) ? $info->loadbefore : [],
	"description" => isset($info->description) ? $info->description : "",
	"website" => isset($info->website) ? $info->website : "",
	"prefix" => isset($info->prefix) ? $info->prefix : $NAME,
	"load" => isset($info->load) ? $info->load : "POSTWORLD",
	"version" => $VERSION,
	"commands" => isset($info->commands) ? $info->commands : [],
	"permissions" => $permissions,
]));
addDir($phar, "src", "src");
addDir($phar, "entry", "entry");
addDir($phar, "resources", "resources");

require_once "src/WorldEditArt/Utils/GeneralUtils.php";
require_once "src/WorldEditArt/Lang/Translation.php";
require_once "src/WorldEditArt/Lang/LanguageFileParser.php";
foreach(glob("resources/lang/*.xml") as $xml){
	$basename = basename($xml);
	printf("[%d] Compiling alternate JSON language file $basename.json...\n", ++$i);
	$parser = new LanguageFileParser(file_get_contents($xml));
	$json = $parser->toJSON();
	$phar->addFromString($xml . ".json", $json);
}

/** @var PharFileInfo $info */
foreach($phar as $info){
	if($info->getSize() >= 8 << 10){
		$info->compress(Phar::GZ);
	}
}

$phar->stopBuffering();

echo "Phar created at " . realpath($file);

if(is_file("priv/postCompile.php")){
	include "priv/postCompile.php";
}
