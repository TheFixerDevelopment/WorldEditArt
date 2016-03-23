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

namespace WorldEditArt\Command;

use pocketmine\item\Item;
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\BlockStream\Cassette;
use WorldEditArt\Objects\BlockStream\WeightedBlockList;
use WorldEditArt\Objects\BlockStream\WeightedBlockType;
use WorldEditArt\Objects\BlockStream\WeightedListBlockChanger;
use WorldEditArt\User\WorldEditArtUser;

class SetSubCommand extends SubCommand{
	public function getName() : string{
		return "set";
	}

	public function getAliases() : array{
		return ["s"];
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_SET_DESCRIPTION);
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_SET_USAGE);
	}

	public function getDetailedUsage(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_SET_DETAILED_USAGE);
	}

	public function run(WorldEditArtUser $user, string ...$args){
		$params = new CommandParser($args);
		$sel = $user->getSelection($selName = $params->opt("n", "default"));
		if($sel === null){
			return $user->translate(Terms::COMMAND_ERROR_NO_SEL, ["NAME" => $selName]);
		}
		$seed = $params->opt("s");
		$seed = $seed === null ? time() : crc32($seed);
		$list = new WeightedBlockList($seed);
		while(($type = $params->nextPlain()) !== null){
			$weight = 1.0;
			if(strpos($type, ";") !== false){
				list($weight, $type) = explode(";", $type, 2);
				$weight = (float) $weight;
			}
			$item = Item::fromString($type);
			if(($block = $item->getBlock()) === Item::AIR and strtoupper($type) !== "AIR"){
				return $user->translate(Terms::COMMAND_ERROR_UNKNOWN_BLOCK, ["TYPE" => $type]);
			}
			$blockType = new WeightedBlockType($weight, $block->getId(), $block->getDamage());
			$list->add($blockType);
		}
		$changer = new WeightedListBlockChanger($list);
		$cassette = new Cassette(($hollow = $params->enabled("h")) ?
			$sel->getHollowBlockStream($padding = $params->opt("p", 1), $margin = $params->opt("m", 0)) :
			$sel->getSolidBlockStream(), $changer);
		$user->getQueue()->insert($cassette);
		return $user->translate(Terms::COMMAND_SET_PENDING, ["COUNT" => $hollow ? "?" : $sel->getApproxBlockCount()]);
	}

	public function hasPermission(WorldEditArtUser $user){
		return $user->hasPermission(PermissionNames::COMMAND_SET);
	}
}
