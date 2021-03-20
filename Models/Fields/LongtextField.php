<?php

namespace Djaravel\Models\Fields;

/**
 * A LongtextField is a special type of VarcharField with a max length of -1
 * which will efectively skip length validation
 */
class LongtextField extends VarcharField
{
	function __construct($nullable = false, $verboseName){
		parent::__construct($nullable, -1, $verboseName)
	}
}