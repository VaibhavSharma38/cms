<?php

namespace xepan\cms;

class page_configuration extends \xepan\base\Page{
	public $title = "Configuration";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
        $site_offline = $tabs->addTab('Put Site Offline');
        $page_offline = $tabs->addTab('Restrict Pages');


        // RESTRICT SITE
		$config_m = $site_offline->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'site_offline'=>'Line',
						'offline_site_content'=>'xepan\base\RichText',
						'continue_crons'=>'Checkbox',
						],
				'config_key'=>'FRONTEND_WEBSITE_STATUS',
				'application'=>'cms'
		]);
		
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();

		$form = $site_offline->add('Form');
		$form->addField('Dropdown','put_site_offline')->setValueList([true=>'Yes',false=>'No'])->setEmptyText('Please select a value')->set($config_m['site_offline']);
		$form->addField('xepan\base\RichText','offline_content')->set($config_m['offline_site_content']);
		$form->addField('Checkbox','continue_crons')->set($config_m['continue_crons']);
		$form->addSubmit('Save');

		if($form->isSubmitted()){
			$config_m['site_offline'] = $form['put_site_offline'];
			$config_m['offline_site_content'] = $form['offline_content'];
			$config_m['continue_crons'] = $form['continue_crons'];
			$config_m->save();

			$form->js()->univ()->successMessage('Saved')->execute();
		}
		
		// RESTRICT PAGES
		$page_config_m = $page_offline->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'page_name'=>'Line',
						'navigate_to_page'=>'Line',
						'restrict_page_content'=>'xepan\base\RichText',
						'based_on_ip'=>'xepan\base\Form_Field_NoValidateDropDown'
						],
				'config_key'=>'FRONTEND_RESTRICTED_PAGES',
				'application'=>'cms'
		]);
		
		$page_config_m->add('xepan\hr\Controller_ACL');
		$page_config_m->tryLoadAny();

		$crud = $page_offline->add('CRUD',['entity_name'=>'Restricted Pages']);
		$crud->setModel($page_config_m);
		$crud->grid->removeColumn('id');
		
		if($crud->isEditing()){
			$form = $crud->form;
			$form->getElement('based_on_ip')->setValueList([true=>'Yes',false=>'No'])->setFieldHint('Restrict pages based upon status (active/InActive) country and states');
		}
	}
}