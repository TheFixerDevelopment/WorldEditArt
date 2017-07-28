<?php

/*
 *
 * WorldEditArt
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace LegendsOfMCPE\WorldEditArt\Epsilon\LibgeomAdapter;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use sofe\libgeom\Shape;
use sofe\libgeom\shapes\CuboidShape;

abstract class ShapeDescriptor{
	const FORMAT_USER_DEFINITION = 1;
	const FORMAT_USER_RANGE = 2;
	const FORMAT_DEBUG = 3;

	public static function describe(IShape $shape, int $format) : ShapeDescriptor{
		return self::_describe($shape, $format);
	}

	/**
	 * @param IShape|Shape|ShapeWrapper $shape
	 * @param int                       $format
	 *
	 * @return string
	 */
	private static function _describe($shape, int $format) : string{
		if($shape instanceof ShapeWrapper){
			return self::_describe($shape->getBaseShape(), $format);
		}
		assert($shape instanceof Shape);
		$baseColor = TextFormat::GOLD;
		$em1 = TextFormat::AQUA;
		$em2 = TextFormat::LIGHT_PURPLE;
		$em3 = TextFormat::BLUE;
		switch(true){
			case $shape instanceof CuboidShape:
				switch($format){
					case self::FORMAT_USER_DEFINITION:
						return "{$baseColor}Cuboid ({$em1}pos1: " . self::formatVector($shape->getFrom()) . "{$baseColor}, " .
							"{$em2}pos2: " . self::formatVector($shape->getTo()) .
							" {$baseColor}in world {$em3}" . self::nameLevel($shape->getLevel());
					case self::FORMAT_USER_RANGE:
						return "{$baseColor}Cuboid {$em1}from " . self::formatVector($shape->getMin()) .
							" {$em2}to pos2: " . self::formatVector($shape->getMax()) .
							" {$baseColor}in world {$em3}" . self::nameLevel($shape->getLevel());
					case self::FORMAT_DEBUG:
						return "Cuboid(from={$shape->getFrom()}, to={$shape->getTo()}, level=" . self::nameLevel($shape->getLevel()) . ")";
				}
				throw self::unknownFormat($format);
			// TODO implement more
		}
	}

	private static function formatVector(Vector3 $vector) : string{
		return "(" . round($vector->x, 2) . ", " . round($vector->y, 2) . ", " . round($vector->z, 2) . ")";
	}

	private static function nameLevel(Level $level) : string{
		return $level->getName() . ($level->getFolderName() === $level->getName() ? "" : " ({$level->getFolderName()})");
	}

	private static function unknownFormat(int $format){
		return new \InvalidArgumentException("Unknown format $format");
	}
}
