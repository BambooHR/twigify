<?php

/**
 * Twigify.  Copyright (c) 2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

namespace BambooHR\Twigify;


/**
 * Class SwitchToIfConverter
 *
 * Converts switch($expr) {
 * 	case 1:
 *  case 2:
 *     doSomething();
 *     break;
 *  case 3:
 *     doSomethingElse();
 *     break;
 *  default:
 *     defaultCase()
 *     break;
 *
 * to
 *
 *  if($expr==1 || $expr==2) {
 *    doSomething();
 *  } elseif ($expr==3) {
 *    doSomethingElse();
 *  } else {
 *    defaultCase();
 *  }
 *
 * Note: if the switch expression includes function calls or assignments then the repeated
 * references to it could have semantic differences from the original switch.
 *
 */
class SwitchToIfConverter extends \PhpParser\NodeVisitorAbstract
{

	function removeFinalBreak(array $stmts) {
		if(count($stmts)>0 && end($stmts) instanceof \PhpParser\Node\Stmt\Break_) {
			return array_slice($stmts,0,-1);
		} else {
			throw new \Exception("Each case must end with a break");
		}
	}
	function buildIf(\PhpParser\Node\Stmt\If_ $rootIf=null, \PhpParser\Node\Stmt\Else_ $default=null, \PhpParser\Node\Expr $cond=null, \PhpParser\Node\Stmt\Case_ $case,array $others ) {
		$isDefault = false;
		foreach($others as $otherCond) {
			if($otherCond==null) {
				$isDefault=true;
			}
		}
		if($isDefault || $case->cond==null) {
			$default = new \PhpParser\Node\Stmt\Else_( $this->removeFinalBreak( $case->stmts) );
		} else {
			$equals = new \PhpParser\Node\Expr\BinaryOp\Equal($cond, $case->cond);
			foreach ($others as $otherCond) {
				$equals =
					new \PhpParser\Node\Expr\BinaryOp\LogicalOr(
						$equals,
						new \PhpParser\Node\Expr\BinaryOp\Equal($cond, $otherCond)
					);
			}
			if($rootIf) {
				$rootIf->elseifs [] = new \PhpParser\Node\Stmt\ElseIf_($equals, $this->removeFinalBreak($case->stmts));
			} else {
				$rootIf = new \PhpParser\Node\Stmt\If_($equals, ["stmts"=>$this->removeFinalBreak($case->stmts)] );
			}
		}


		return [$rootIf, $default];
	}

	public function enterNode(\PhpParser\Node $node) {
		$others=[];
		/** @var \PhpParser\Node\Stmt\If_ $rootIf */
		$rootIf = $default = null;
		if ($node instanceof \PhpParser\Node\Stmt\Switch_) {
			/** @var \PhpParser\Node\Stmt\Case_ $case */
			foreach ($node->cases as $index => $case) {
				if (count($case->stmts)==0) {
					// Deal with 2 case statements in a row, by simply making a list.
					$others[] = $case->cond;
				}
				if (count($case->stmts) >= 1) {
					list($rootIf,$default) = $this->buildIf($rootIf, $default, $node->cond, $case, $others);
				}
			}
		}
		if($rootIf && $default) {
			$rootIf->else = $default;
		}
		return $rootIf;
	}



}
