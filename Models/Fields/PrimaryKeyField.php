<?php 

namespace Djaravel\Models\Fields;

class PrimaryKeyField extends Field
{
	
	/**
	 * __construct
	 *
	 * @param  String $verboseName
	 * 
	 * @return void
	 */
	function __construct($verboseName = null){
		parent::__construct(true, false, $verboseName);
	}
}