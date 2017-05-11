<?php
/**
 * Twigify.  Copyright (c) 2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

namespace BambooHR\Twigify;

class Indenter {
	private $indent = 0;
	private $indentSequence = "\t";
	private $inTag = false;
	private $inCloseTag = false;

	const TYPE_NONE = 0;
	const TYPE_TAG  = 1;
	const TYPE_TEXT = 2;
	const TYPE_WHITESPACE = 3;
	const TYPE_STATEMENT = 4;
	const TYPE_EXPRESSION = 5;
	const TYPE_COMMENT = 6;

	private $last = self::TYPE_NONE;

	private $output = "";

	function setIndent($str, $count) {
		$this->indentSequence = str_repeat($str, $count);
	}

	function indent() {
		$this->indent++;
	}

	function getOutput() {
		return trim($this->output);
	}

	function unindent() {
		$this->indent--;
	}

	function append($str) {
		$this->output .= $str;
	}

	function newLine() {
		$this->output = rtrim($this->output)."\n". str_repeat($this->indentSequence, $this->indent);
	}

	function statement($str) {
		if(!$this->inTag) {
			$this->newLine();
		}
		$this->output.= "{% " . $str . " %}";
		$this->last = self::TYPE_STATEMENT;
	}

	function expression($str) {
		if(!$this->inTag && $this->last!=self::TYPE_TEXT) {
			$this->newLine();
		}
		$this->output.= "{{ " . $str . " }}";
		$this->last = self::TYPE_EXPRESSION;
	}

	function comment($comment) {
		$this->newLine();

		if(substr($comment,0,2)=="//") {
			$comment =substr($comment,2);
		} else if(substr($comment,0,2)=="/*" && substr($comment,-2)=="*/") {
			$comment = substr($comment,2,-2);
		}
		$comment = trim($comment);
		$this->output .= "{# ".str_replace("#}","", $comment) . " #}";
		$this->last = self::TYPE_COMMENT;
	}

	function html($str) {
		for ($i = 0; $i < strlen($str); ++$i) {
			switch ($str[$i]) {
				case '<':
					$out="<";
					if (!$this->inTag) {
						$this->inTag = true;
						if ($i+1 < strlen($str) && $str[$i+1]=='/') {
							$out .= $str[++$i];
							$this->inCloseTag = true;
							$this->indent--;
							$this->newLine();
						} else {
							$this->newLine();
							$this->indent++;
						}
					}
					$this->output.=$out;
					break;
				case '>':

					$this->output .= ">";
					if ($this->inTag) {
						$this->inTag = false;
						$this->inCloseTag = false;
						$this->last = self::TYPE_TAG;
					}
					$this->newLine();
					break;

				case "\n":
				case "\r":
				case "\t":
				case ' ' :
					if (!$this->inTag) {
						if ($this->last == self::TYPE_STATEMENT) {
							$this->newLine();
						} else {
							$this->output .= $str[$i];
						}
						if($this->last!=self::TYPE_TEXT) {
							$this->last=self::TYPE_WHITESPACE;
						}
					} else {
						$this->output = rtrim($this->output)." ";
					}

					break;
				default:
					if(!$this->inTag) {
						$this->last = self::TYPE_TEXT;
					}
					$this->output .= $str[$i];
					break;
			}
		}
	}
}