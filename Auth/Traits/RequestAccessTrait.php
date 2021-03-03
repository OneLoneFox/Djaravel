<?php

namespace Djaravel\Auth\Traits;

use \Djaravel\Core\Exceptions\ImproperlyConfiguredException;
use \Djaravel\Utils\Shortcuts;

trait RequestAccessTrait
{
	function dispatch(...$args){
		if( !isset($_SESSION['user']) ){
			echo "no user session";
			$this->handleNoPermission();
		}
		if( !$this->hasPermission() ){
			echo "no permission";
			$this->handleNoPermission();
		}
		parent::dispatch(...$args);
	}
	
	private function hasPermission(){
		if( !isset($this->permissionRequired) ){
			throw new ImproperlyConfiguredException(
				sprintf(
					'%s is missing the permissionRequired attribute. Define %s.permissionRequired, or override %s.hasPermission().',
					self::class, self::class, self::class
				)
			);
		}
		return $_SESSION['user']->permission == $this->permissionRequired;
	}

	private function handleNoPermission(){
		if( !isset($this->loginUrl) ){
			throw new ImproperlyConfiguredException(
				sprintf(
					'%s is missing the loginUrl attribute. Define %s.loginUrl, or override %s.handleNoPermission().',
					self::class, self::class, self::class
				)
			);
		}
		Shortcuts::redirectBase($this->loginUrl);
	}
}