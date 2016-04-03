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

use WorldEditArt\Utils\GeneralUtils;
use WorldEditArt\WorldEditArt;

class LanguageFileParser{
	/** @var WorldEditArt $main */
	private $main;
	/** @var resource $parser */
	private $parser;

	/** @var string|null $buffer */
	private $buffer = null;
	private $expectingAuthors = false, $expectingConstants = false, $valuesMode = false, $justEndedElement = false;
	private $stack = [];
	private $sinceStack = [], $updatedStack = [];
	private $lastParams = [];
	private $lastConstantName = "";

	/** @var string $name */
	private $name;
	/** @var string $version */
	private $version;
	/** @var string $relEng */
	private $relEng;
	/** @var string[] $authors */
	private $authors = [];
	private $constants = [];
	/** @var Translation[] $values */
	private $values = [];

	/**
	 * LanguageFileParser constructor.
	 *
	 * @param string            $xml
	 * @param WorldEditArt|null $main
	 * @param bool              $isXml
	 */
	public function __construct(string $xml, $main = null, bool $isXml = true){
		$this->main = $main;
		if(!$isXml){
			$this->fromJSON($xml);
			return;
		}
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($this->parser, [$this, "startEl"], [$this, "endEl"]);
		xml_set_character_data_handler($this->parser, [$this, "rawText"]);
		xml_parse($this->parser, $xml, true);
		$error = xml_get_error_code($this->parser);
		if($error !== XML_ERROR_NONE){
			throw new \RuntimeException("Error parsing language file: " . xml_error_string($error));
		}
		xml_parser_free($this->parser);
	}

	public function startEl(/** @noinspection PhpUnusedParameterInspection */
		$parser, string $name, array $attr){
		$attr = array_change_key_case($attr, CASE_LOWER);
		if($this->valuesMode){
			$this->stack[] = $name;
			$this->buffer = "";
			if(isset($attr["since"])){
				$this->sinceStack[count($this->stack) - 1] = $attr["since"];
			}
			if(isset($attr["updated"])){
				$this->updatedStack[count($this->stack) - 1] = $attr["updated"];
			}
			if(isset($attr["vars"])){
				$this->lastParams = explode(",", $attr["vars"]);
			}
			$this->justEndedElement = false;
		}elseif($name === "language"){
			$this->name = $attr["name"];
			$this->version = $attr["version"];
			$this->relEng = $attr["rel"] ?? $this->version;
		}elseif($name === "authors"){
			$this->expectingAuthors = true;
		}elseif($this->expectingAuthors and $name === "author"){
			$this->buffer = "";
		}elseif($name === "constants"){
			$this->expectingConstants = true;
		}elseif($this->expectingConstants and $name === "constant"){
			if(!isset($attr["name"])){
				$line = xml_get_current_line_number($this->parser);
				if($this->main !== null){
					$this->main->getLogger()->warning("Error on line $line of language file: Constant does not have a name");
				}else{
					echo "[WARNING] Error on line $line of language file: Constant does not have a name\n";
				}
			}
			$this->lastConstantName = $attr["name"];
			$this->buffer = "";
		}elseif($name === "values"){
			$this->valuesMode = true;
		}
	}

	public function endEl(/** @noinspection PhpUnusedParameterInspection */
		$parser, string $name){
		if($this->valuesMode){
			$key = implode(".", $this->stack);
			array_pop($this->stack);
//			echo "Exited " . $name . ", key $key, buffer $this->buffer\n";

			if(!$this->justEndedElement){
//				echo $key, PHP_EOL;
				$value = trim($this->buffer);
				if(strlen($value) > 0){
					$this->values[$key] = new Translation($key, $value, GeneralUtils::valueForMaxKeyInArray($this->sinceStack, $this->getVersion()), GeneralUtils::valueForMaxKeyInArray($this->updatedStack, $this->getVersion()), $this->lastParams);
				}
				$this->buffer = null;
			}

			if(isset($this->sinceStack[$offset = count($this->stack)])){
				unset($this->sinceStack[$offset]);
			}
			if(isset($this->updatedStack[$offset = count($this->stack)])){
				unset($this->updatedStack[$offset]);
			}
			$this->justEndedElement = true;
		}
		if($this->expectingAuthors and $name === "author"){
			$this->authors[] = trim($this->buffer);
			$this->buffer = null;
		}elseif($name === "authors"){
			$this->expectingAuthors = false;
		}elseif($this->expectingConstants and $name === "constant"){
			$this->constants[$this->lastConstantName] = trim($this->buffer);
			$this->buffer = null;
		}elseif($name === "constants"){
			$this->expectingConstants = false;
		}elseif($name === "values"){
			$this->valuesMode = false;
		}
	}

	public function rawText(/** @noinspection PhpUnusedParameterInspection */
		$parser, string $data){
		if($this->buffer !== null){
			$this->buffer .= $data;
		}
	}

	public function getName(){
		return $this->name;
	}

	public function getVersion(){
		return $this->version;
	}

	public function getRelevantEnglishVersion(){
		return $this->relEng;
	}

	public function getAuthors(){
		return $this->authors;
	}

	public function getValues(){
		return $this->values;
	}

	public function getConstants(){
		return $this->constants;
	}

	public function finalize(){
		unset($this->parser, $this->buffer, $this->expectingAuthors, $this->expectingConstants, $this->valuesMode, $this->justEndedElement, $this->stack, $this->sinceStack, $this->updatedStack, $this->lastConstantName);
	}

	public function toJSON() : string{
		return json_encode([
			"name" => $this->name,
			"version" => $this->version,
			"relEng" => $this->relEng,
			"authors" => $this->authors,
			"constants" => $this->constants,
			"values" => array_map(function (Translation $translation){
				return $translation->toJSONArray();
			}, $this->values),
		], JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	private function fromJSON(string $json){
		$data = json_decode($json, true);
		$this->name = $data["name"];
		$this->version = $data["version"];
		$this->relEng = $data["relEng"];
		$this->authors = $data["authors"];
		$this->constants = $data["constants"];
		foreach($data["values"] as $k => $value){
			$this->values[$k] = Translation::fromJSONArray($value);
		}
	}
}
