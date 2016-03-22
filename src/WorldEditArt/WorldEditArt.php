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

use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use WorldEditArt\Command\WorldEditArtCommand;
use WorldEditArt\DataProvider\DataProvider;
use WorldEditArt\DataProvider\Model\Zone;
use WorldEditArt\DataProvider\SerializedDataProvider;
use WorldEditArt\Lang\LanguageManager;
use WorldEditArt\User\PlayerUser;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\Utils\Fridge;

class WorldEditArt extends PluginBase{
	const MIN_Y = 0;
	const MAX_Y = 127;

	private static $PLUGIN_NAME = "WorldEditArt";

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

	/** @var WorldEditArtUser[] $playerUsers */
	public $playerUsers = [];

	public static function getPluginName() : string{
		return self::$PLUGIN_NAME;
	}

	public function onLoad(){
		self::$PLUGIN_NAME = $this->getName();
		assert(in_array($this->getServer()->getName(), ["PocketMine-MP", "PocketMine-Soft"]), "Haters Gonna Hate");
	}

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->langMgr = new LanguageManager($this);
		$this->fridge = new Fridge($this);
		$this->dataProvider = new SerializedDataProvider($this);
		$this->command = new WorldEditArtCommand($this);
		$this->listener = new EventListener($this);
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
	public function createZone(string $name, int $type, Level $level, Vector3 $start, Vector3 $end) : Zone{
		if($this->dataProvider->isZoneExistent($name)){
			throw new \InvalidArgumentException("Zone of same name already exists");
		}
		$zone = new Zone($name, $type, $level->getName(), $start, $end);
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
}
