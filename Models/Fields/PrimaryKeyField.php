<?php 

namespace Djaravel\Models\Fields;

class PrimaryKeyField extends Field{
	function __construct(){
		parent::__construct(true, false);
	}
}