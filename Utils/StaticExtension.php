<?php

namespace Djaravel\Utils;
use \Twig\Extension\AbstractExtension;

class StaticExtension extends AbstractExtension
{
	public function getFunctions(){
		return [
			new \Twig\TwigFunction('static', function($url){
				return '/'.$_ENV['BASE_DIR'].'/'.$_ENV['STATIC_URL'].'/'.$url;
			})
		];
	}
}