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

namespace WorldEditArt\User;

use WorldEditArt\Objects\BlockStream\Cassette;
use WorldEditArt\Utils\BulkJob;

class CassetteQueue implements BulkJob{
	const DIRECTION_FORWARDS = false;
	const DIRECTION_BACKWARDS = true;

	/** @var WorldEditArtUser $user */
	private $user;

	/** @var int $undoCounter the number of cassettes to undo */
	private $undoCounter = 0;

	/** @var Cassette[] $undoQueue contains the history of cassettes played */
	private $undoQueue = [];
	/**
	 * If $undoCounter > 0, execute [0] in reverse direction
	 *
	 * @var Cassette[] $execQueue
	 */
	private $execQueue = [];
	/** @var Cassette[] $redoQueue */
	private $redoQueue = [];

	public function __construct(WorldEditArtUser $user){
		$this->user = $user;
	}

	public function insert(Cassette $cassette){
		$this->redoQueue = [];
		$this->execQueue[] = $cassette;
	}

	public function undo() : bool{
		if($this->undoCounter >= count($this->undoQueue) + (isset($this->execQueue[0]) ? 1 : 0)){ // undo queue + current executing
			return false; // nothing to undo
		}
		if(count($this->execQueue) > 1){
			array_unshift($this->redoQueue, array_pop($this->execQueue)); // postpone the scheduled execution
			return true;
		}
		$this->undoCounter++;
		return true;
	}

	public function redo() : bool{
		if($this->undoCounter === 0){
			if(count($this->redoQueue) === 0){
				return false; // nothing to undo
			}
			$this->execQueue[] = array_shift($this->redoQueue); // actual redo
		}else{
			$this->undoCounter--; // remove the undo, de facto redo
		}
		return true;
	}

	public function doOnce(){
		if($this->undoCounter > 0){
			assert(isset($this->execQueue[0]));
			$change = $this->execQueue[0]->previous();
			if($change === null){
				$this->undoCounter--;
				$undone = array_shift($this->execQueue);
				array_unshift($this->redoQueue, $undone);
				if($this->undoCounter > 0){
					array_unshift($this->execQueue, array_pop($this->undoQueue)); // move next undo to execution
				}
			}else{
				if($this->user->canBuild($change[0])){
					$change[0]->getLevel()->setBlock($change[0], $change[0], false, false);
				}else{
					// TODO handle error
				}
			}
		}elseif(isset($this->execQueue[0])){
			$change = $this->execQueue[0]->next();
			if($change === null){
				$this->undoQueue[] = array_shift($this->execQueue);
			}else{
				if($this->user->canBuild($change[1])){
					$change[1]->getLevel()->setBlock($change[1], $change[1], false, false);
				}else{
					// TODO handle error
				}
			}
		}
	}

	public function hasMore() : bool{
		return !$this->user->isClosed();
	}

	public function __debugInfo(){
		return [
			"user" => $this->user->getUniqueName(),
			"undoCounter" => $this->undoCounter,
			"undoQueue" => $this->undoQueue,
			"execQueue" => $this->execQueue,
			"redoQueue" => $this->redoQueue,
		];
	}
}
