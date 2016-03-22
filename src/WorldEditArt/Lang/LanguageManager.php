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

class LanguageManager{
	/** @var WorldEditArt $main */
	private $main;

	/** @var Translation[][] $translations */
	private $translations = [];

	/** @var LanguageFileParser[] $langs */
	private $langs = [];

	public function __construct(WorldEditArt $main){
		$this->main = $main;

		$dir = $main->getDataFolder() . "lang/";
		if(!is_dir($dir)){
			mkdir($dir);
			$it = new \DirectoryIterator($main->getResourceFolder("lang/"));
			/** @var \SplFileInfo $file */
			foreach($it as $file){
				copy($file->getPathname(), $dir . $file->getBasename());
			}
		}

		foreach(new \DirectoryIterator($dir) as $file){
			if($file->getExtension() === "xml"){
				$this->main->getLogger()->info("Loading {$file->getBasename()}...");
				$this->parse(file_get_contents($file->getPathname()));
			}
		}

		if(!isset($this->langs["en"])){
			$this->main->getLogger()->alert("en.xml missing in lang folder! Default en.xml will be loaded as fallback language.");
			$this->parse($this->main->getResourceContents("lang/en.xml"), "en");
		}
		$this->parse($this->main->getResourceContents("lang/en.xml"), "/backup");
//		var_dump(array_keys($this->langs));
	}

	private function parse(string $langFile, $forceName = null){
		$parser = new LanguageFileParser($langFile, $this->main);
		$name = $forceName ?? $parser->getName();
		foreach($parser->getValues() as $id => $term){
			if(!isset($this->translations[$id][$name])){
				foreach($parser->getConstants() as $constant => $value){
					$term->define($constant, $value);
				}
				$this->translations[$id][$name] = $term;
			}
		}

		$parser->finalize();
//		foreach($parser->getValues() as $value){
//			echo json_encode(["id" => $value->getId(), "value" => $value->getValue(), "since" => $value->getSince(), "updated" => $value->getUpdated()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
//		}
		$this->langs[$name] = $parser;
	}

	public function getTerm(string $id, array $langs = [], array $vars = []) : string{
		if(isset($this->translations[$id])){
			$trans = $this->translations[$id];
			foreach($langs as $lang){
				if(isset($trans[$lang])){
					return $trans[$lang]->toString($vars);
				}
			}
			if(isset($trans["/backup"])){
				return $trans["/backup"]->toString($vars);
			}else{
				$this->main->getLogger()->error("Failed to find undefined string '$id' (from languages: " . implode(", ", $langs) . ", default en)");
				return $id;
			}
		}else{
			$this->main->getLogger()->error("Failed to find undefined string '$id'");
			return $id;
		}
	}
}
