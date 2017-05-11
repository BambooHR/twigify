<?php

/**
 * Twigify.  Copyright (c) 2016-2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

namespace BambooHR\Twigify;

use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Stmt;
use PhpParser\Node\Name;


class Converter extends \PhpParser\PrettyPrinter\Standard {

	private $errors = [];
	protected $indenter;

	public function __construct(array $options = []) {
		parent::__construct($options);
		$this->indenter=new Indenter();
	}


	protected function error($message, Node $node) {
		$this->errors[]= get_class($node).": $message on line ".$node->getLine();
	}

	function getErrors() {
		return $this->errors;

	}

    // Names
    public function pName(Name $node) {
        return implode('\\', $node->parts);
    }

    public function pName_FullyQualified(Name\FullyQualified $node) {
        return '\\' . implode('\\', $node->parts);
    }

    public function pName_Relative(Name\Relative $node) {
        return 'namespace\\' . implode('\\', $node->parts);
    }

    // Magic Constants

    public function pScalar_MagicConst_Class(MagicConst\Class_ $node) {
        return 'constant("__CLASS__")';
    }

    public function pScalar_MagicConst_Dir(MagicConst\Dir $node) {
        return 'constant("__DIR__")';
    }

    public function pScalar_MagicConst_File(MagicConst\File $node) {
        return 'constant("__FILE__")';
    }

    public function pScalar_MagicConst_Function(MagicConst\Function_ $node) {
        return 'constant("__FUNCTION__")';
    }

    public function pScalar_MagicConst_Line(MagicConst\Line $node) {
        return 'constant("__LINE__")';
    }

    public function pScalar_MagicConst_Method(MagicConst\Method $node) {
        return 'constant("__METHOD__")';
    }

    public function pScalar_MagicConst_Namespace(MagicConst\Namespace_ $node) {
        return 'constant("__NAMESPACE__")';
    }

    public function pScalar_MagicConst_Trait(MagicConst\Trait_ $node) {
        return 'constant("__TRAIT__")';
    }

    // Scalars

    public function pScalar_String(Scalar\String_ $node) {
        return '\'' . $this->pNoIndent(addcslashes($node->value, "\'\n\\")) . '\'';
    }

    public function pScalar_Encapsed(Scalar\Encapsed $node) {
        return '"' . $this->pEncapsList($node->parts, '"') . '"';
    }

    public function pScalar_LNumber(Scalar\LNumber $node) {
        return (string) $node->value;
    }

    public function pScalar_DNumber(Scalar\DNumber $node) {
        $stringValue = sprintf('%.16G', $node->value);
        if ($node->value !== (double) $stringValue) {
            $stringValue = sprintf('%.17G', $node->value);
        }

        // ensure that number is really printed as float
        return preg_match('/^-?[0-9]+$/', $stringValue) ? $stringValue . '.0' : $stringValue;
    }

    // Assignments

    public function pExpr_Assign(Expr\Assign $node) {
		$this->indenter->statement("set ".$this->pInfixOp('Expr_Assign', $node->var, ' = ', $node->expr));
    }

    public function pExpr_AssignRef(Expr\AssignRef $node) {
        return $this->pInfixOp('Expr_AssignRef', $node->var, ' = ', $node->expr);
    }

	public function __call($name, $arguments) {
		echo "$name";
		print_r($arguments);
		$this->error("Not supported: $name", $arguments[0]);
	}

	public function pExpr_AssignOp_Concat(AssignOp\Concat $node) {
		$this->indenter->statement("set ".$this->p($node->var)."  = (".$this->pInfixOp('Expr_BinaryOp_Concat', $node->var, ' ~ ', $node->expr).")");
    }


    public function pExpr_AssignOp_BitwiseOr(AssignOp\BitwiseOr $node) {	
        $this->indenter->statement("set ".$this->p($node->var)."=".$this->pInfixOp('Expr_BinaryOp_BitwiseOr', $node->var, ' b-or ', $node->expr));
    }

    // Binary expressions

    public function pExpr_BinaryOp_Plus(BinaryOp\Plus $node) {
        return $this->pInfixOp('Expr_BinaryOp_Plus', $node->left, ' + ', $node->right);
    }

    public function pExpr_BinaryOp_Minus(BinaryOp\Minus $node) {
        return $this->pInfixOp('Expr_BinaryOp_Minus', $node->left, ' - ', $node->right);
    }

    public function pExpr_BinaryOp_Mul(BinaryOp\Mul $node) {
        return $this->pInfixOp('Expr_BinaryOp_Mul', $node->left, ' * ', $node->right);
    }

    public function pExpr_BinaryOp_Div(BinaryOp\Div $node) {
        return $this->pInfixOp('Expr_BinaryOp_Div', $node->left, ' / ', $node->right);
    }

    public function pExpr_BinaryOp_Concat(BinaryOp\Concat $node) {
        return $this->pInfixOp('Expr_BinaryOp_Concat', $node->left, ' ~ ', $node->right);
    }

    public function pExpr_BinaryOp_Mod(BinaryOp\Mod $node) {
        return $this->pInfixOp('Expr_BinaryOp_Mod', $node->left, ' % ', $node->right);
    }

    public function pExpr_BinaryOp_BooleanAnd(BinaryOp\BooleanAnd $node) {
        return $this->pInfixOp('Expr_BinaryOp_BooleanAnd', $node->left, ' and ', $node->right);
    }

    public function pExpr_BinaryOp_BooleanOr(BinaryOp\BooleanOr $node) {
        return $this->pInfixOp('Expr_BinaryOp_BooleanOr', $node->left, ' or ', $node->right);
    }

    public function pExpr_BinaryOp_BitwiseAnd(BinaryOp\BitwiseAnd $node) {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseAnd', $node->left, ' b-and ', $node->right);
    }

    public function pExpr_BinaryOp_BitwiseOr(BinaryOp\BitwiseOr $node) {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseOr', $node->left, ' b-or ', $node->right);
    }

    public function pExpr_BinaryOp_BitwiseXor(BinaryOp\BitwiseXor $node) {
        return $this->pInfixOp('Expr_BinaryOp_BitwiseXor', $node->left, ' b-xor ', $node->right);
    }

    public function pExpr_BinaryOp_Pow(BinaryOp\Pow $node) {
        return $this->pInfixOp('Expr_BinaryOp_Pow', $node->left, ' ** ', $node->right);
    }

    public function pExpr_BinaryOp_LogicalAnd(BinaryOp\LogicalAnd $node) {
        return $this->pInfixOp('Expr_BinaryOp_LogicalAnd', $node->left, ' and ', $node->right);
    }

    public function pExpr_BinaryOp_LogicalOr(BinaryOp\LogicalOr $node) {
        return $this->pInfixOp('Expr_BinaryOp_LogicalOr', $node->left, ' or ', $node->right);
    }

    public function pExpr_BinaryOp_LogicalXor(BinaryOp\LogicalXor $node) {
        return $this->pInfixOp('Expr_BinaryOp_LogicalXor', $node->left, ' xor ', $node->right);
    }

    public function pExpr_BinaryOp_Equal(BinaryOp\Equal $node) {
        return $this->pInfixOp('Expr_BinaryOp_Equal', $node->left, ' == ', $node->right);
    }

    public function pExpr_BinaryOp_NotEqual(BinaryOp\NotEqual $node) {
        return $this->pInfixOp('Expr_BinaryOp_NotEqual', $node->left, ' != ', $node->right);
    }

    public function pExpr_BinaryOp_Identical(BinaryOp\Identical $node) {
        return $this->pInfixOp('Expr_BinaryOp_Identical', $node->left, ' sameas ', $node->right);
    }

    public function pExpr_BinaryOp_NotIdentical(BinaryOp\NotIdentical $node) {
        return $this->pInfixOp('Expr_BinaryOp_NotIdentical', $node->left, ' not sameas ', $node->right);
    }

    public function pExpr_BinaryOp_Greater(BinaryOp\Greater $node) {
        return $this->pInfixOp('Expr_BinaryOp_Greater', $node->left, ' > ', $node->right);
    }

    public function pExpr_BinaryOp_GreaterOrEqual(BinaryOp\GreaterOrEqual $node) {
        return $this->pInfixOp('Expr_BinaryOp_GreaterOrEqual', $node->left, ' >= ', $node->right);
    }

    public function pExpr_BinaryOp_Smaller(BinaryOp\Smaller $node) {
        return $this->pInfixOp('Expr_BinaryOp_Smaller', $node->left, ' < ', $node->right);
    }

    public function pExpr_BinaryOp_SmallerOrEqual(BinaryOp\SmallerOrEqual $node) {
        return $this->pInfixOp('Expr_BinaryOp_SmallerOrEqual', $node->left, ' <= ', $node->right);
    }

    // Unary expressions

    public function pExpr_BooleanNot(Expr\BooleanNot $node) {
        return $this->pPrefixOp('Expr_BooleanNot', ' not ', $node->expr);
    }


    public function pExpr_UnaryMinus(Expr\UnaryMinus $node) {
        return $this->pPrefixOp('Expr_UnaryMinus', '-', $node->expr);
    }

    public function pExpr_UnaryPlus(Expr\UnaryPlus $node) {
        return $this->pPrefixOp('Expr_UnaryPlus', '+', $node->expr);
    }

    public function pExpr_ErrorSuppress(Expr\ErrorSuppress $node) {
        return $this->pPrefixOp('Expr_ErrorSuppress', '', $node->expr);
    }

    // Casts

    public function pExpr_Cast_Int(Cast\Int_ $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_Double(Cast\Double $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_String(Cast\String_ $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_Array(Cast\Array_ $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_Object(Cast\Object_ $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_Bool(Cast\Bool_ $node) {
		return $this->p( $node->expr );
    }

    public function pExpr_Cast_Unset(Cast\Unset_ $node) {
        return $this->p( $node->expr );
    }

    // Function calls and similar constructs

    public function pExpr_FuncCall(Expr\FuncCall $node) {
		if($node->name=="__js") {
			return "__(".$this->pCommaSeparated($node->args) .") | e('js')";
		}
		if($node->name=="__raw") {
			return "__(".$this->pCommaSeparated($node->args) .") | raw";
		}
		if($node->name=='abs') {
			return "(".$this->p($node->args[0])."| abs)";
		}
		if($node->name=='array_keys') {
			return "(".$this->p($node->args[0]).")|keys";
		}
		if($node->name=='array_slice') {
			return "(".$this->p($node->args[0]).")|slice(".$this->pCommaSeparated(array_slice($node->args,1)).")";
		}
		if($node->name=='count') {
			return "( ".$this->p($node->args[0])." | length ) ";
		}
		if($node->name=='empty') {
			return "(".$this->p($node->args[0])." is empty) ";
		}
		if($node->name=='he') {
			return $this->p($node->args[0]);
		}
		if($node->name=='implode') {
			if(count($node->args)==2) {
				return "(".$this->p($node->args[1]).") | join(".$this->p($node->args[0]).")";
			}
		}
		if($node->name=='explode') {
			if(count($node->args)==2) {
				return "(".$this->p($node->args[1]).") | split(".$this->p($node->args[0]).")";
			}
		}

		if($node->name=='in_array') {
			return "(" .$this->p($node->args[0]) ." in ".$this->p($node->args[1]).")";
		}
		if($node->name=='json_encode') {
			return "(".$this->p($node->args[0]).") | json_encode)";
		}
		if($node->name=='nl2br') {
			return "(".$this->p($node->args[0]).")|nl2br";
		}
		if($node->name=='number_format') {
			return "(".$this->p($node->args[0]).")|number_format(".$this->pCommaSeparated(array_slice($node->args,1)).")";
		}
		if($node->name=='round') {
			return "(".$this->p($node->args[0]).")|round(".$this->pCommaSeparated(array_slice($node->args,1)).")";
		}
		if($node->name=='sprintf') {
			return "(".$this->p($node->args[0]).") | format(".$this->pCommaSeparated(array_slice($node->args,1)).")";
		}
		if($node->name=='printf') {
			$this->indenter->statement(
				"(".$this->p($node->args[0]).") | format(".$this->pCommaSeparated(array_slice($node->args,1)).")"
			);
		}
		if($node->name=='str_replace') {
			return "(".$this->p($node->args[2])."| replace(".$this->p($node->args[0]).",".$this->p($node->args[1])."))";
		}
		if($node->name=='strtoupper') {
			return "(".$this->p($node->args[0])."| capitalize)";
		}

		if($node->name=='trim') {
			return "(".$this->p($node->args[0])."| trim)";
		}
		if($node->name=='ucfirst') {
			return "(".$this->p($node->args[0]).")|capitalize";
		}
		if($node->name=='url_encode') {
			return "(".$this->p($node->args[0]).")|url_encode";
		}


		return $this->pCallLhs($node->name)
             . '(' . $this->pCommaSeparated($node->args) . ')';
    }

    public function pExpr_MethodCall(Expr\MethodCall $node) {
        return $this->pDereferenceLhs($node->var) . '.' . $this->pObjectProperty($node->name)
             . '(' . $this->pCommaSeparated($node->args) . ')';
    }


    public function pExpr_Empty(Expr\Empty_ $node) {
        return '(' . $this->p($node->expr) . ' is empty)';
    }

    public function pExpr_Isset(Expr\Isset_ $node) {
        return '(' . $this->pCommaSeparated($node->vars) . ' is defined)';
    }

    public function pExpr_Include(Expr\Include_ $node) {
		$value = $node->expr;
		if($value instanceof Scalar\String_) {
			$value->value = preg_replace('/\.php$/', '.html.twig', $value->value);
		}
		return $this->indenter->statement("include ".$this->p($value));
    }

    public function pExpr_List(Expr\List_ $node) {

        $pList = array();
        foreach ($node->vars as $var) {
            if (null === $var) {
                $pList[] = '';
            } else {
				if($var instanceof Expr\Variable) {
					$pList[] = $this->p($var);
				} else {
					$this->error("No support", $node);
				}
            }
        }

        return implode(', ', $pList);
    }

    // Other

    public function pExpr_Variable(Expr\Variable $node) {
        if ($node->name instanceof Expr) {
			$this->error("No support", $node);
        } else {
            return $node->name;
        }
    }

    public function pExpr_Array(Expr\Array_ $node) {
		$assoc=false;
		foreach($node->items as $item) {
			if($item->key) { $assoc=true; }
		}
		if($assoc) {
			return '{' . $this->pCommaSeparated($node->items) . '}';
		} else {
			return '[' . $this->pCommaSeparated($node->items) . ']';
		}
    }

    public function pExpr_ArrayItem(Expr\ArrayItem $node) {
        return (null !== $node->key ? $this->p($node->key) . ' : ' : '')
             . $this->p($node->value);
    }

    public function pExpr_ArrayDimFetch(Expr\ArrayDimFetch $node) {
        return $this->pDereferenceLhs($node->var)
             . '[' . (null !== $node->dim ? $this->p($node->dim) : '') . ']';
    }

    public function pExpr_ConstFetch(Expr\ConstFetch $node) {
		if(strcasecmp($node->name,'false')==0 || strcasecmp($node->name,'true')==0 || strcasecmp($node->name,'null')==0) {
			return strval($node->name);
		} else {
			return "constant(\"" . $this->p($node->name) . "\")";
		}
    }

    public function pExpr_ClassConstFetch(Expr\ClassConstFetch $node) {
        return "constant(\"".$this->p($node->class) . '::' . $node->name."\")";
    }

    public function pExpr_PropertyFetch(Expr\PropertyFetch $node) {
        return $this->pDereferenceLhs($node->var) . '.' . $this->pObjectProperty($node->name);
    }


    public function pExpr_Ternary(Expr\Ternary $node) {
        // a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
        // this is okay because the part between ? and : never needs parentheses.
        return $this->pInfixOp('Expr_Ternary',
            $node->cond, ' ?' . (null !== $node->if ? ' ' . $this->p($node->if) . ' ' : '') . ': ', $node->else
        );
    }


    // Declarations

    public function pStmt_Namespace(Stmt\Namespace_ $node) {
		return "";
    }

    public function pStmt_Use(Stmt\Use_ $node) {
		return "";
    }

    public function pStmt_GroupUse(Stmt\GroupUse $node) {
		return "";
    }



    public function pStmt_Function(Stmt\Function_ $node) {
		$ret="macro ".$node->name."(";
		foreach($node->params as $index=>$param) {
			if($index>0) $ret.=",";
			/** @var Node\Param $param */
			$ret.=$param->name;
			if($param->default) {
				$ret.=$this->p($param->default);
			}
		}
		$ret.=")";
		$this->indenter->statement($ret);
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
		$this->indenter->statement("endmacro");
        return $ret;
    }

    // Control flow

    public function pStmt_If(Stmt\If_ $node) {
		$this->indenter->statement("if " . $this->p($node->cond));
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
		foreach($node->elseifs as $stmt) {
			$this->p($stmt);
		}
		if($node->else) {
			$this->p($node->else);
		}
		$this->indenter->statement("endif");
    }

    public function pStmt_ElseIf(Stmt\ElseIf_ $node) {
		$this->indenter->statement('elseif ' . $this->p($node->cond) . ')' );
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
    }

    public function pStmt_Else(Stmt\Else_ $node) {
		$this->indenter->statement("else");
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
    }

	private function getForMax(Stmt\For_ $node) {
		/** @var BinaryOp $op2 */
		$op2 = $node->cond[0];

		if($op2 instanceof BinaryOp\GreaterOrEqual || $op2 instanceof BinaryOp\SmallerOrEqual) {
			return $this->p($op2->right);
		}

		if($op2 instanceof BinaryOp\Smaller) {
			return $this->p($op2->right)."-1";
		}
		if($op2 instanceof BinaryOp\Greater) {
			return $this->p($op2->right)."-1";
		}
		return "";
	}

	private function getForStep($name, Stmt\For_ $node) {
		$op2 = $node->loop[0];
		if ($op2 instanceof Expr\PostInc || $op2 instanceof Expr\PreInc) {
			if($op2->var->name!=$name) {
				$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
			}
			$step = 1;
		} else if ($op2 instanceof Expr\PostDec || $op2 instanceof Expr\PreDec) {
			if($op2->var->name!=$name) {
				$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
			}
			$step = -1;
		} else if ($op2 instanceof \PhpParser\Node\Expr\AssignOp\Plus) {
			if($op2->var->name!=$name) {
				$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
			}
			$step = $this->p($op2->expr);
		} else if ($op2 instanceof \PhpParser\Node\Expr\AssignOp\Minus) {
			if($op2->var->name!=$name) {
				$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
			}
			$step = "-" . $this->p($op2->expr);
		} else {
			if ($op2 instanceof Expr\Assign) {
				if ($op2->expr instanceof BinaryOp\Plus) {
					$step = $this->p($op2->expr->right);
				} else if ($op2->expr instanceof BinaryOp\Minus) {
					$step = "-".$this->p($op2->expr->right);
				}
			}
		}
		return $step;
	}

    public function pStmt_For(Stmt\For_ $node) {

		if(! (
				count($node->init) == 1 &&
				count($node->loop) == 1 &&
				count($node->cond) == 1 &&
				($node->cond[0] instanceof BinaryOp && $node->cond[0]->left instanceof Expr\Variable)
			)
		) {
			$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
		}

		$name = $node->init[0]->var->name;
		if($name!= $node->cond[0]->left->name) {
			$this->error("For loop only supports the pattern: for(\$i=#; \$i {op} #; \$i+=#) ;", $node);
		}
		if($node->init[0])

		$loopCounter = $this->p($node->init[0]->var);
		$min = $this->p($node->init[0]->expr);
		$max = $this->getForMax($node);
		$step = $this->getForStep($name, $node);

		$this->indenter->statement("for $loopCounter in range($min, $max, $step)");
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
		$this->indenter->statement("endfor");
    }
	
	protected function pStmts(array $nodes, $indent = true) {
        foreach ($nodes as $node) {
            $this->pComments($node->getAttribute('comments', array()));
			if ($node instanceof Expr) {
				if ($node instanceof Expr\FuncCall) {
					$this->indenter->expression( $this->pExpr_FuncCall($node) );
				} else if ($node instanceof Expr\Assign || $node instanceof Expr\AssignOp) {
					$this->p( $node );
				} else {
					$this->error("Unsupported", $node);
				}
			} else {
				$this->p($node);
			}
        }
    }

    public function pStmt_Foreach(Stmt\Foreach_ $node) {
		$this->indenter->statement('for '
             . (null !== $node->keyVar ? $this->p($node->keyVar) . ' , ' : '')
             . $this->p($node->valueVar) 
			 . " in ". $this->p($node->expr)
		);
		$this->indenter->indent();
		$this->pStmts($node->stmts);
		$this->indenter->unindent();
		$this->indenter->statement("endfor");
    }

    public function pStmt_Return(Stmt\Return_ $node) {
		$this->indenter->expression($this->p($node->expr));
    }

    // Other

    public function pStmt_Echo(Stmt\Echo_ $node) {
		$this->indenter->expression($this->pImplode($node->exprs, ' ~ '));
    }

    public function pStmt_InlineHTML(Stmt\InlineHTML $node) {
		$str = str_replace("{{", "{{ '{{' }}", $node->value);
		$str = str_replace("{%", "{{ '{%' }}", $str);
		$this->indenter->html( $str );
    }

    // Helpers

    protected function pType($node) {
        return is_string($node) ? $node : $this->p($node);
    }

    protected function pObjectProperty($node) {
        if ($node instanceof Expr) {
            return '{' . $this->p($node) . '}';
        } else {
            return $node;
        }
    }

    protected function pEncapsList(array $encapsList, $quote) {
        $return = '';
        foreach ($encapsList as $element) {
            if ($element instanceof Scalar\EncapsedStringPart) {
                $return .= addcslashes($element->value, "\n\r\t\f\v$" . $quote . "\\");
            } else {
                $return .= '{' . $this->p($element) . '}';
            }
        }

        return $return;
    }

    protected function pDereferenceLhs(Node $node) {
        if ($node instanceof Expr\Variable
            || $node instanceof Name
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\PropertyFetch
            || $node instanceof Expr\StaticPropertyFetch
            || $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\Array_
            || $node instanceof Scalar\String_
            || $node instanceof Expr\ConstFetch
            || $node instanceof Expr\ClassConstFetch
        ) {
            return $this->p($node);
        } else  {
            return '(' . $this->p($node) . ')';
        }
    }

    protected function pCallLhs(Node $node) {
        if ($node instanceof Name) {
			return $this->p($node);
        } else  {
			$this->error("Not supported", $node);
        }
    }
	
	 /**
     * Prints reformatted text of the passed comments.
     *
     * @param Comment[] $comments List of comments
     *
     * @return string Reformatted text of comments
     */
    protected function pComments(array $comments) {
        foreach ($comments as $comment) {
        	$this->indenter->comment($comment);
        }
    }

    public function prettyPrint(array $stmts) {
		$this->pStmts( $stmts );
		return $this->indenter->getOutput();
	}
}

