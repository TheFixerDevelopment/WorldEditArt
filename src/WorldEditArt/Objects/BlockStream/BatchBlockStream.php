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

namespace WorldEditArt\Objects\BlockStream;

class BatchBlockStream implements BlockStream{
	/** @var BlockStream[] $streams */
	private $streams;

	/**
	 * @param BlockStream[] $streams
	 */
	public function __construct(array $streams){
		$this->streams = $streams;
	}

	public function next(){
		next:
		if(count($this->streams) === 0){
			return null;
		}
		$next = $this->streams[0]->next();
		if($next === null){
			array_shift($this->streams);
			goto next;
		}
		return $next;
	}
}
