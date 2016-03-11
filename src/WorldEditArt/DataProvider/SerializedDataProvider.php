<?php

/*
 * WorldEditArt
 *
 * Copyright (C) 2016 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace WorldEditArt\DataProvider;

use pocketmine\math\Vector3;
use WorldEditArt\DataProvider\Model\UserData;
use WorldEditArt\DataProvider\Model\Zone;
use WorldEditArt\WorldEditArt;

class SerializedDataProvider implements DataProvider{
	/** @var WorldEditArt $main */
	private $main;
	/** @var string $userPath */
	private $userPath;
	/** @var string $zonePath */
	private $zonePath;
	/** @var Zone[] $zones */
	private $zones;

	public function __construct(WorldEditArt $main){
		$this->main = $main;

		$this->userPath = $main->getDataFolder() . "users/";
		if(!is_dir($this->userPath)){
			mkdir($this->userPath);
		}
		$this->zonePath = $main->getDataFolder() . "zones/";
		if(!is_dir($this->zonePath)){
			mkdir($this->zonePath);
		}
		$this->loadZones();
	}

	public function getUserData(string $type, string $name) : UserData{
		$data = new UserData($type, $name);
		if(is_file($file = $this->getUserFile($type, $name))){
			$json = self::readData($file, true);
			$data->langs = $json["langs"];
			$data->itemActions = $json["itemActions"];
		}
		return $data;
	}

	public function saveUserData(UserData $data){
		if(!is_dir($dir = $this->userPath . $data->type)){
			mkdir($dir);
		}
		self::writeData($this->getUserFile($data->type, $data->name), [
			"type" => $data->type,
			"name" => $data->name,
			"itemActions" => $data->itemActions,
			"langs" => $data->langs,
		]);
	}

	public function getUserFile(string $type, string $name) : string{
		return $this->userPath . $type . "/" . $name . ".usr";
	}

	private function loadZones(){
		/** @var \SplFileInfo $file */
		foreach(new \RegexIterator(new \DirectoryIterator($this->zonePath), '%\.wzn$%i', \RegexIterator::USE_KEY) as $file){
			$data = self::readData($file);
			$zone = new Zone($data->name, $data->type, $data->levelName, new Vector3(...$data->start), new Vector3(...$data->end));
			$this->zones[$zone->getName()] = $zone;
		}
	}

	public function getZones() : array{
		return $this->zones;
	}

	public function isZoneExistent(string $name) : bool{
		return isset($this->zones[$name]);
	}

	public function addZone(Zone $zone) : bool{
		$this->zones[$zone->getName()] = $zone;
		$start = $zone->getStart();
		$end = $zone->getEnd();
		self::writeData($this->getZoneFile($zone->getName()), [
			"name" => $zone->getName(),
			"type" => $zone->getType(),
			"levelName" => $zone->getLevelName(),
			"start" => [$start->x, $start->y, $start->z],
			"end" => [$end->x, $end->y, $end->z],
		]);
	}

	public function removeZone(string $zoneName) : bool{
		if(!isset($this->zones[$zoneName])){
			return false;
		}
		unset($this->zones[$zoneName]);
		if(is_file($path = $this->getZoneFile($zoneName))){
			return unlink($path);
		}
		return false;
	}

	public function getZone(string $zoneName){
		return $this->zones[$zoneName] ?? null;
	}

	public function getZoneFile(string $name) : string{
		return $this->zonePath . $name . ".wzn";
	}

	const MODE_RAW = "CMPR:\0";
	const MODE_GZIP = "CMPR:\1";
	const MODE_DEFLATE = "CMPR:\2";

	public static function writeData(string $file, $data){
		return self::writeFile($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_BIGINT_AS_STRING));
	}

	public static function writeFile($file, $contents){
		return self::writeFileMode($file, $contents, strlen($contents) > 4089 ? self::MODE_DEFLATE : self::MODE_RAW);
	}

	private static function writeFileMode(string $file, string $contents, string $mode){
		if($mode === self::MODE_GZIP){
			$contents = zlib_encode($contents, \ZLIB_ENCODING_GZIP);
		}elseif($mode === self::MODE_DEFLATE){
			$contents = zlib_encode($contents, \ZLIB_ENCODING_DEFLATE);
		}
		return file_put_contents($file, $mode . $contents);
	}

	public static function readFile(string $file) : string{
		$contents = file_get_contents($file);
		switch($file){
			case self::MODE_RAW:
				return $contents;
			case self::MODE_GZIP:
			case self::MODE_DEFLATE:
				return zlib_decode($contents);
		}
		throw new \UnexpectedValueException("No format prefix in file");
	}

	public static function readData(string $file, bool $assoc = false){
		return json_decode(self::readFile($file), $assoc);
	}
}
