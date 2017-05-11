<?php

/**
 * Twigify.  Copyright (c) 2016-2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

namespace BambooHR\Twigify;
use PhpParser\Node\Stmt;


class JinjaConverter extends Converter
{
	public function pStmt_ElseIf(Stmt\ElseIf_ $node) {
		$this->indenter->statement("elif " . $this->p($node->cond) . ')', true );
		$this->pStmts($node->stmts);
	}

	public function pStmt_Foreach(Stmt\Foreach_ $node) {
		if (null !== $node->keyVar) {
			$this->indenter->statement(
				'for '
				. $this->p($node->keyVar) . ' , '
				. $this->p($node->valueVar)
				. " in " . $this->p($node->expr) . '.iteritems()'
			);
			$this->pStmts($node->stmts,true);
			$this->indenter->statement('endfor');
		} else {
			$this->indenter->statement(
				'for '
				. $this->p($node->valueVar)
				. " in " . $this->p($node->expr)
			);
			$this->pStmts($node->stmts,true);
		}
	}
}