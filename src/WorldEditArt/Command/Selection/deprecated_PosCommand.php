<?php

/*
 * WorldEditArt
 *
 * Copyright (C) 2016 PEMapModder
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace WorldEditArt\Command\Selection;

use pocketmine\block\Block;
use WorldEditArt\Command\BlockSubCommand;
use WorldEditArt\Command\CommandParser;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\Space\Cuboid\CuboidSpace;
use WorldEditArt\Objects\Space\Cylinder\Right\RightCylindricalSpace;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\Objects\Space\Sphere\SphereSpace;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

/** @deprecated */
class deprecated_PosCommand extends BlockSubCommand{
	const TYPE_CUBOID = 0;
	const TYPE_CYLINDER = 1;
	const TYPE_SPHERE = 2;

	const PROP_CENTER = 0;
	const PROP_1 = 1;
	const PROP_2 = 2;
	const PROP_RADIUS = 3;
	const PROP_HEIGHT = 4;

	public static $TYPE_NAMES = [
		self::TYPE_CUBOID => ["pos", "cub", "cuboid", ""],
		self::TYPE_CYLINDER => ["c", "cyl", "cylinder"],
		self::TYPE_SPHERE => ["sph", "sphere"],
	];
	public static $TYPE_ENTRY_CONN = ["", "-", "."];
	public static $PROPERTY_NAMES = [
		self::PROP_1 => ["1"],
		self::PROP_2 => ["2"],
		self::PROP_RADIUS => ["r", "rad", "radius"],
		self::PROP_CENTER => ["c", "ctr", "center", "centre"],
		self::PROP_HEIGHT => ["h", "hgt", "height"],
	];

	public static $TYPE_PHRASES = [
		self::TYPE_CUBOID => Terms::PHRASE_SPACE_TYPE_CUBOID,
		self::TYPE_CUBOID => Terms::PHRASE_SPACE_TYPE_CYLINDER_GENERIC,
		self::TYPE_SPHERE => Terms::PHRASE_SPACE_TYPE_SPHERE,
	];
	public static $TYPE_CLASSES = [
		self::TYPE_CUBOID => CuboidSpace::class,
		self::TYPE_CYLINDER => RightCylindricalSpace::class,
		self::TYPE_SPHERE => SphereSpace::class,
	];

	/** @var int $type */
	private $type;
	/** @var int $prop */
	private $prop;
	private $aliases;

	public function __construct(WorldEditArt $main, int $type, int $prop){
		parent::__construct($main);
		$this->type = $type;
		$this->prop = $prop;
	}

	public function getName() : string{
		return self::$TYPE_NAMES[$this->type][0] . self::$TYPE_ENTRY_CONN[0] . self::$PROPERTY_NAMES[$this->prop][0];
	}

	public function getAliases() : array{
		if(isset($this->aliases)){
			return $this->aliases;
		}
		$aliases = [];
		foreach(self::$TYPE_NAMES[$this->type] as $a => $typeName){
			foreach(self::$PROPERTY_NAMES[$this->prop] as $b => $propName){
				foreach(self::$TYPE_ENTRY_CONN as $c => $conn){
					if($a + $b + $c > 0){
						$aliases[] = $typeName . $conn . $propName;
					}
				}
			}
		}
		return $this->aliases = $aliases;
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_POS_DESCRIPTION_FORMULA, [
			"TYPE" => $user->translate(self::$TYPE_NAMES[$this->type]),
			"PROP" => $user->translate(self::$TYPE_NAMES[$this->prop]),
		]);
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_POS_USAGE, [
			"NAME" => $this->getName(),
		]);
	}

	public function hasPermission(WorldEditArtUser $user){
		// TODO: Implement hasPermission() method.
	}

	public function onRun(WorldEditArtUser $user, Block $block, CommandParser $args){
		assert($block->isValid());

		$class = self::$TYPE_CLASSES[$this->type];
		$sel = $user->getSelection($selName = $args->opt("n", "default"));
		if(get_class($sel) !== $class){
			/** @var Space|string $class */
			/** @var Space $sel */
			$sel = $class::create($block->getLevel(), $args, $user);
			$user->setSelection($sel, $selName);
		}
	}
}
