<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\cms;

class Model_Snapshots extends \xepan\base\Model_Table{
	public $table='webpage_snapshot';
	
	public $acl_type = 'SnapShots';

	function init(){
		parent::init();

		$this->hasOne('xepan\cms\Webpage','page_id');
		$this->hasOne('xepan\hr\Employee','created_by_id');
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('content')->type('text');
		$this->addField('page_url');

	}

}
