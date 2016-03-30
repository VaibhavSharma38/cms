<?php

namespace xepan\cms;

class Initiator extends \Controller_Addon {
    
    public $addon_name = 'xepan_cms';

    function init(){
        parent::init();

        if($this->app->is_admin){
            $this->routePages('xepan_cms');
            $this->addLocation(array('template'=>'templates','js'=>'templates/js'))
            ->setBaseURL('../vendor/xepan/cms/');

        }else{

            $this->routePages('xepan_cms');
            $this->addLocation(array('template'=>'templates','js'=>'templates/js'))
            ->setBaseURL('./vendor/xepan/cms/');

            $user = $this->add('xepan\base\Model_User_Active');
            $user->addCondition('scope',['Editor','Both','SuperUser']);
            
            $auth = $this->app->add('BasicAuth');
            $auth->setModel($user,'username','password');

            if(strpos($this->app->page,'_admin_')!==false){
                $old_jui = $this->api->jui->destroy();
                $this->app->jui=null;

                $this->app->template->loadTemplate('html');
                $this->app->add('jUI');

                $l = $this->app->add('Layout_Fluid');
                $m = $l->add('Menu_Horizontal',null,'Top_Menu');
                $m->addItem('Pages','xepan_cms_editor_pages');


                $auth->check();
            }

            $this->app->isEditing = false;
            if($auth->isLoggedIn()) $this->app->isEditing = true;

            
        }        
    }
}
