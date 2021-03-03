<?php 

namespace Djaravel\Models\Fields;

class PrimaryKeyField extends Field{

	function __construct($verboseName = null){
		parent::__construct(true, false, $verboseName);
	}
}