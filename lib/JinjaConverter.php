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
		return ' {% elif ' . $this->p($node->cond) . ') %}'
			. $this->pStmts($node->stmts);
	}

	public function pStmt_Foreach(Stmt\Foreach_ $node) {
		if (null !== $node->keyVar) {
			return '{% for '
				. $this->p($node->keyVar) . ' , '
				. $this->p($node->valueVar)
				. " in " . $this->p($node->expr) . '.iteritems() %}'
				. $this->pStmts($node->stmts) . '{% endfor %}';
		} else {
			return '{% for '
				. $this->p($node->valueVar)
				. " in " . $this->p($node->expr) . ' %}'
				. $this->pStmts($node->stmts) . '{% endfor %}';
		}
	}
}