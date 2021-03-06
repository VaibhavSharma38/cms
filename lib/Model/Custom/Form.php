<?php

namespace xepan\cms;

class Model_Custom_Form extends \xepan\base\Model_Table{

 	public $table = 'custom_form'; 
 	public $status=[
		'Active',
		'InActive'
	];
	// public $acl=false;
	public $actions=[
		'Active'=>['view','edit','delete','enquiry','deactivate'],
		'InActive'=>['view','edit','delete','enquiry','activate'],
	];

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Post_Email_MyEmails','emailsetting_id');
		$this->addField('name');
		$this->addField('submit_button_name');

		$this->addField('form_layout')->enum(array('stacked','minimal','horizontal','empty'));
		$this->addField('custom_form_layout_path');
			
		$this->addField('recieve_email')->type('boolean');
		$this->addField('recipient_email')->hint('comma separated multiple email ids ');
		
		$this->addField('auto_reply')->type('boolean');
		$this->addField('email_subject');
		$this->addField('message_body')->type('text')->display(['form'=>'xepan\base\RichText']);
		$this->addField('created_at')->defaultValue($this->app->now);
		$this->addField('created_by_id')->defaultValue($this->app->employee->id);
		$this->addField('type')->defaultValue('Custom_Form');

		$this->addField('status')->defaultValue('Active');

		$this->hasMany('xepan\cms\Custom_FormField','custom_form_id');
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("CustomForm : '".$this['name']."' now active, For use on website", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_cms_customform")
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("CustomForm '".$this['name']."' has deactivated, not available use on website", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_cms_customform")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}

	function page_enquiry($p){
		$custom_form_sub_m = $p->add('xepan\cms\Model_Custom_FormSubmission');		
		$custom_form_sub_m->addCondition('custom_form_id',$this->id);

		$grid = $p->add('xepan\hr\Grid');
		$grid->setModel($custom_form_sub_m,['value','created_at'])->setOrder('created_at','desc');
		
		$grid->addPaginator(10);

		$grid->addHook('formatRow',function($g){
			$array = json_decode($g->model['value'],true);
			$g->current_row_html['value'] = implode("<br/>",
										array_map(
											    function($k, $v) {
											        return "$k : $v";
											    }, 
											    array_keys($array), 
											      $array
										)
									);
		});
	}
}
