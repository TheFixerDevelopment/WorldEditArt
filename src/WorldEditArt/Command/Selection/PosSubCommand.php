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
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\Space\Cuboid\CuboidSpace;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class PosSubCommand extends BlockSubCommand implements PermissionNames, Terms{
	const POS_1 = "pos1";
	const POS_2 = "pos2";

	public static $TYPE_HUMAN = [
		self::POS_1 => self::PHRASE_SPACE_TYPE_CUBOID,
		self::POS_2 => self::PHRASE_SPACE_TYPE_CUBOID,
	];
	public static $PROPERTY_HUMAN = [
		self::POS_1 => self::PHRASE_SPACE_PROP_POS_1,
		self::POS_2 => self::PHRASE_SPACE_PROP_POS_2,
	];
	public static $ALIASES = [
		self::POS_1 => ["1"],
		self::POS_2 => ["2"],
	];
	public static $PERMISSION = [
		self::POS_1 => self::COMMAND_POS_1,
		self::POS_2 => self::COMMAND_POS_2,
	];
	public static $TYPE_CLASS = [
		self::POS_1 => CuboidSpace::class,
		self::POS_2 => CuboidSpace::class,
	];
	public static $PROPERTY_NAME = [
		self::POS_1 => CuboidSpace::PROP_POS_1,
		self::POS_2 => CuboidSpace::PROP_POS_2,
	];

	public static function getAll(WorldEditArt $main){
		return [
			new PosSubCommand($main, self::POS_1),
			new PosSubCommand($main, self::POS_2),
		];
	}

	/** @var string $name */
	private $name;

	public function __construct(WorldEditArt $main, string $cmdName){
		$this->name = $cmdName;
		parent::__construct($main);
	}

	public function onRun(WorldEditArtUser $user, Block $block, CommandParser $params){
		/** @var string|Space $class */
		$class = self::$TYPE_CLASS[$this->name];
		$selName = $params->nextPlain() ?? "default";
		$sel = $user->getSelection($selName);
		if(get_class($sel) !== $class){
			$old = $sel;
			$sel = $class::instantiate($block->getLevel());
			if($old !== null){
				$user->sendMessage(Terms::COMMAND_POS_DISPLACED, ["OLD" => $old->describe($user)]);
			}
			$user->setSelection($sel, $selName);
		}
		$success = $sel->handlePosCommand($prop = self::$PROPERTY_NAME[$this->name], $block);
		assert($success, "Undefined property $prop of space " . get_class($sel));
		$user->sendMessage(self::COMMAND_POS_SUCCESS, ["SPACE" => $user->translate($sel)]);
	}

	public function getName() : string{
		return $this->name;
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return $user->translate(self::COMMAND_POS_DESCRIPTION_FORMULA, [
			"TYPE" => $user->translate(self::$TYPE_HUMAN[$this->name]),
			"PROP" => $user->translate(self::$PROPERTY_HUMAN[$this->name]),
		]);
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return $user->translate(self::COMMAND_POS_USAGE, ["NAME" => $this->name]);
	}

	public function getAliases() : array{
		return self::$ALIASES[$this->name];
	}

	public function hasPermission(WorldEditArtUser $user){
		return $user->hasPermission(self::$PERMISSION[$this->name]);
	}
}
