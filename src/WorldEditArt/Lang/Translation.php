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

namespace WorldEditArt\Lang;

use WorldEditArt\WorldEditArt;

class Translation{
	/** @var string $id */
	private $id, $value;
	/** @var string $since */
	private $since, $updated;
	private $params = [];

	public function __construct(string $id, string $value, string $since, string $updated, array $params){
		$this->id = $id;
		$value = preg_replace("/[\r\n][\r\n\t ]+/", " ", $value);
		$value = str_replace("\\n", "\n", $value);
		$value = str_replace("\\t", "    ", $value);
		$this->value = $value;
		$this->since = $since;
		$this->updated = $updated;
		$this->params = $params;
		if(WorldEditArt::isDebug()){
			assert(preg_match('/[0-9]+\.[0-9]+', $this->since), "Missing or corrupted updated attribute for translation $this->id");
			assert(preg_match('/[0-9]+\.[0-9]+', $this->updated), "Missing or corrupted updated attribute for translation $this->id");
		}

	}

	public function getId() : string{
		return $this->id;
	}

	public function getValue() : string{
		return $this->value;
	}

	public function getSince() : string{
		return $this->since;
	}

	public function getUpdated() : string{
		return $this->updated;
	}

	public function define(string $constant, string $value){
		$this->value = str_replace($constant, $value, $this->value);
	}

	public function toString(array $vars = []) : string{
		if(WorldEditArt::isDebug()){
			foreach($this->params as $param){
				if(!isset($vars[$param])){
					throw new \InvalidArgumentException("Missing parameter $param");
				}
			}
		}
		$value = $this->value;
		foreach($vars as $varName => $var){
			$value = str_replace("\${" . $varName . "}", $var, $value);
		}
		return $value;
	}
}
