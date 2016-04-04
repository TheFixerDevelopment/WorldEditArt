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
 * @author LegendsOfMCPE Team
 */

namespace WorldEditArt;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use WorldEditArt\Command\WorldEditArtCommand;
use WorldEditArt\DataProvider\DataProvider;
use WorldEditArt\DataProvider\Model\Zone;
use WorldEditArt\DataProvider\SerializedDataProvider;
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\Lang\LanguageManager;
use WorldEditArt\User\PlayerUser;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\Utils\Fridge;

define('WorldEditArt\NO_XML', isset(getopt("", ["worldeditart.noxml"])["worldeditart.noxml"]));
define('WorldEditArt\XML_SUPPORTED', !NO_XML and function_exists("xml_parser_create"), true);
define('WorldEditArt\LANG_EXTENSION', XML_SUPPORTED ? "xml" : "json", true);
define('WorldEditArt\LANG_SUFFIX', XML_SUPPORTED ? ".xml" : ".xml.json", true);

class WorldEditArt extends PluginBase{
	const MIN_Y = 0;
	const MAX_Y = 127;

	private static $PLUGIN_NAME = "WorldEditArt";
	private static $debug = false;

	/** @var LanguageManager $langMgr */
	private $langMgr;
	/** @var Fridge $fridge */
	private $fridge;
	/** @var DataProvider $dataProvider */
	private $dataProvider;
	/** @var WorldEditArtCommand $command */
	private $command;
	/** @var EventListener $listener */
	private $listener;
	/** @var int[] */
	private $itemNamesCache = [];
	/** @var string[] */
	private $symbolsCache = [];

	/** @var WorldEditArtUser[] $playerUsers */
	public $playerUsers = [];

	public static function getPluginName() : string{
		return self::$PLUGIN_NAME;
	}

	public static function isDebug() : bool{
		return self::$debug;
	}

	public function onLoad(){
		self::$PLUGIN_NAME = $this->getName();
		assert(in_array($this->getServer()->getName(), ["PocketMine-MP", "PocketMine-Soft"]), "Haters Gonna Hate");
		self::$debug = $this->getServer()->getConfigBoolean("worldeditart.debug") or isset(getopt("", ["worldeditart.debug"])["worldeditart.debug"]);
		if(self::$debug){
			$this->getLogger()->info("Loading with debug mode");
		}
	}

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->langMgr = new LanguageManager($this);
		$this->fridge = new Fridge($this);
		$this->dataProvider = new SerializedDataProvider($this);
		$this->command = new WorldEditArtCommand($this);
		$this->listener = new EventListener($this);

		if(WorldEditArt::isDebug()){
			foreach((new \ReflectionClass(PermissionNames::class))->getConstants() as $name){
				assert($this->getServer()->getPluginManager()->getPermission($name) !== null, "Permission $name doesn't exist");
			}
		}
	}

	public function getResourceFolder(string $file = "") : string{
		return $this->getFile() . "resources/" . $file;
	}

	public function getResourceContents(string $path){
		$res = $this->getResource($path);
		if(is_resource($res)){
			$contents = stream_get_contents($res);
			fclose($res);
			return $contents;
		}
		return null;
	}

	public function getLanguageManager() : LanguageManager{
		return $this->langMgr;
	}

	public function getFridge() : Fridge{
		return $this->fridge;
	}

	public function getDataProvider() : DataProvider{
		return $this->dataProvider;
	}

	public function getMainCommand() : WorldEditArtCommand{
		return $this->command;
	}

	public function getListener() : EventListener{
		return $this->listener;
	}

	public static function getInstance(Server $server) : WorldEditArt{
		return ($instance = $server->getPluginManager()->getPlugin(self::$PLUGIN_NAME)) !== null and $instance->isEnabled() ?
			$instance : null;
	}

	public function translate(string $id, array $langs = [], array $vars = []) : string{
		return $this->getLanguageManager()->getTerm($id, $langs, $vars);
	}

	/**
	 * @param Player $player
	 *
	 * @return WorldEditArtUser|null
	 */
	public function getPlayerUser(Player $player){
		return $this->playerUsers[strtolower($player->getName())] ?? null;
	}

	public function addPlayerUser(PlayerUser $user){
		$this->playerUsers[strtolower($user->getName())] = $user;
	}

	/**
	 * @param string       $name
	 * @param int          $type
	 * @param Level|string $level
	 * @param Vector3      $start
	 * @param Vector3      $end
	 *
	 * @return Zone
	 *
	 * @throws \InvalidArgumentException if zone of the same name already exists
	 */
	public function createZone(string $name, int $type, $level, Vector3 $start, Vector3 $end) : Zone{
		if($this->dataProvider->isZoneExistent($name)){
			throw new \InvalidArgumentException("Zone of same name already exists");
		}
		$zone = new Zone($name, $type, $level instanceof Level ? $level->getName() : $level, $start, $end);
		return $this->dataProvider->addZone($zone) ? $zone : null;
	}

	public function getZones(Position $position) : array{
		$result = [];
		foreach($this->dataProvider->getZones() as $zone){
			if($zone->isInside($position)){
				$result[$zone->getName()] = $zone->getType();
			}
		}
		return $result;
	}

	/**
	 * @param Position $from     old position of movement
	 * @param Position $to       new position of movement
	 * @param int[]    &$entered output parameter for zones entered: [zone name => zone type]
	 * @param int[]    &$left    output parameter for zones left: [zone name => zone type]
	 */
	public function compareZones(Position $from, Position $to, &$entered, &$left){
		$entered = [];
		$left = [];
		foreach($this->dataProvider->getZones() as $zone){
			$fromInside = $zone->isInside($from);
			$toInside = $zone->isInside($to);
			if($fromInside and !$toInside){
				$left[$zone->getName()] = $zone->getType();
			}elseif(!$fromInside and $toInside){
				$entered[$zone->getName()] = $zone->getType();
			}
		}
	}

	public function preprocessUserInput(array $args) : array{
		if(!isset($this->symbolsCache)){
			$this->symbolsCache = (new Config($this->getFile() . "resources/translated/symbols.properties", Config::PROPERTIES));
		}
		foreach($args as &$arg){
			$arg = str_replace(array_keys($this->symbolsCache), array_values($this->symbolsCache), $arg);
		}
		return $args;
	}

	public function getItemNames(){
		if(!isset($this->itemNamesCache)){
			return $this->itemNamesCache = (new Config($this->getFile() . "resources/translated/itemNames.properties", Config::PROPERTIES))->getAll();
		}
		return $this->itemNamesCache;
	}

	public function getBlockIdByName(string $name){
		$block = Item::fromString($name)->getBlock();
		if(strtolower($name) === "air" or $block->getId() !== Block::AIR){
			return $block->getId();
		}
		$id = $this->itemNamesCache[str_replace("_", " ", $name)];
		$block = Item::get($id)->getBlock();
		if($id === 0 or $block->getId() !== Block::AIR){
			return $block->getId();
		}
		return null;
	}

	public static function rotateClockwise(int $side){
		switch($side){
			case Vector3::SIDE_NORTH:
				return Vector3::SIDE_EAST;
			case Vector3::SIDE_SOUTH:
				return Vector3::SIDE_WEST;
			case Vector3::SIDE_WEST:
				return Vector3::SIDE_NORTH;
			case Vector3::SIDE_EAST:
				return Vector3::SIDE_SOUTH;
			default:
				return $side;
		}
	}

	public static function rotateAntiClockwise(int $side){
		switch($side){
			case Vector3::SIDE_SOUTH:
				return Vector3::SIDE_EAST;
			case Vector3::SIDE_NORTH:
				return Vector3::SIDE_WEST;
			case Vector3::SIDE_EAST:
				return Vector3::SIDE_NORTH;
			case Vector3::SIDE_WEST:
				return Vector3::SIDE_SOUTH;
			default:
				return $side;
		}
	}

	public static function getDirectionVector(Location $loc) : Vector3{
		$y = -sin(deg2rad($loc->pitch));
		$xz = cos(deg2rad($loc->pitch));
		$x = -$xz * sin(deg2rad($loc->yaw));
		$z = $xz * cos(deg2rad($loc->yaw));

		return (new Vector3($x, $y, $z))->normalize();
	}

	public static function getDirection(Location $loc) : int{
		$rotation = ($loc->yaw - 90) % 360;
		if($rotation < 0){
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2; //North
		}elseif(45 <= $rotation and $rotation < 135){
			return 3; //East
		}elseif(135 <= $rotation and $rotation < 225){
			return 0; //South
		}elseif(225 <= $rotation and $rotation < 315){
			return 1; //West
		}else{
			return null;
		}
	}

	public static function itemTypeHash(int $id, int $damage) : int{
		return ($damage << 16) | ($id & 0xFFFF);
	}

	public static function itemTypeDeHash(int $hash, int &$id, int &$damage){
		$id = $hash & 0xFFFF;
		$damage = $hash >> 16;
	}
}
