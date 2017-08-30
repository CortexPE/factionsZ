<?php
namespace EschieEsh\factionsZ\dataprovider;

use EschieEsh\factionsZ\factionsZ;
abstract class DataProvider{

	/** @var factionsZ */
	protected $plugin;
    
	public function __construct(factionsZ $plugin){
		$this->plugin = $plugin;
	}
	
	public abstract function close();
	
}
