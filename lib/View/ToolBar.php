<?php


namespace xepan\cms;


class View_ToolBar extends \View {

	function init(){
		parent::init();
		
        $this->app->jui->addStylesheet('elfinder.full');
        $this->app->jui->addStylesheet('elfindertheme');
        $this->app->jui->addStylesheet('xepan-editing');


		$this->app->jui->addStaticInclude('elfinder.full');
		$this->js(true)
				->_load('tinymce.min')
				->_load('jquery.tinymce.min')
				->_load('xepan-richtext-admin')
				->_load('shortcut')
				// ->_load('xepanEditor')
				// ->_load('xepanComponent')
				->_load('jquery.livequery')
				;
		$this->app->jui->addStaticInclude('xepanEditor');
		$this->app->jui->addStaticInclude('xepanComponent');

		$tools = $this->app->getFrontEndTools();

		$view = $this->add('AbstractController');

		$tools_array = [];
		//tools_array
		foreach (array_keys($tools) as $group) {
			$tools_array[$group] = [];

			foreach ($tools[$group] as $key => $tool) {

				$t_v = $view->add($tool);
				$t_v->getOptionPanel($view,null);

				$tool_arr = explode("\\", $tool);
				$tool_name = array_pop($tool_arr);
				$tool_name = str_replace("Tool_", '', $tool_name);
				$tool_namespace = implode("/", $tool_arr);

				$drop_html = $t_v->runatServer ? '<div class="xepan-component xepan-serverside-component" xepan-component-name="'.$tool_name.'" xepan-component="'.str_replace('\\', '/', get_class($t_v)).'">' .$t_v->getHTML(). '</div>': $t_v->getHTML();
				$tools_array[$group][$tool] = [
											'name'=>$tool_name,
											'drop_html'=>$drop_html,
											'icon_img'=>'./vendor/'.$tool_namespace.'/templates/images/'.$tool_name.'_icon.png'
										];

			}
		}

		$component_selector="body .xepan-component";
		$editing_template = null;

		if($this->app->editing_template){
			$editing_template = $this->app->editing_template;
			$component_selector="body.xepan-component, body .xepan-component";
			$this->js(true)->_selector('body')->addClass('xepan-component xepan-sortable-component');
			$this->js(true)->_selector('.xepan-page-wrapper')->removeClass('xepan-component xepan-sortable-component');
		}

		$this->js(true)
			// ->_load('xepanComponent')
			->_load('xepanEditor')
			->xepanEditor([
				'base_url'=>$this->api->url()->absolute()->getBaseURL(),
				'file_path'=>$this->app->page_object instanceof \xepan\cms\page_cms?realpath($this->app->page_object->template->origin_filename):'false',
				'template_file'=>$this->app->page_object instanceof \xepan\cms\page_cms?realpath($this->app->template->origin_filename):'false',
				'template'=>$this->app->page_object instanceof \xepan\cms\page_cms?$this->app->template->template_file:'false',
				'save_url'=> $this->api->url()->absolute()->getBaseURL().'?page=xepan/cms/admin/save_page&cut_page=1',
				'template_editing'=> isset($this->app->editing_template),
				'tools'=>$tools_array
			]);

		$this->js(true)->xepanComponent(['editing_template'=>$editing_template,'component_selector'=>$component_selector])->_selector($component_selector);
	}

	function initold(){
		parent::init();

		$this->js(true)
				->_load('tinymce.min')
				->_load('jquery.tinymce.min')
				->_load('xepan-richtext-admin');

		$this->js(true)->_load('shortcut');

		$this->app->jui->addStaticInclude('elfinder.full');
        $this->app->jui->addStylesheet('elfinder.full');
        $this->app->jui->addStylesheet('elfindertheme');
		// $this->js(true)->_load('elfinder.full');
  //       $this->js(true)->_css('elfinder.full');
        // $this->js(true)->_css('elfindertheme');

        // $this->js(true)->_css('jquery-ui');
        // $this->js(true)->_css('font-awesome');

		//load style css
		$this->app->jui->addStaticStyleSheet($this->api->url()->absolute()->getBaseURL().'vendor/xepan/cms/templates/css/xepan-editing.css');
		// $this->js(true)->_css();
		$this->js(true)
				->_load($this->api->url()->absolute()->getBaseURL().'vendor/xepan/cms/templates/js/xepanTool.js');

		$group_tpl = $this->template->cloneRegion('group');
		$this->template->del('group');
		$tool_tpl = $this->template->cloneRegion('tool');
		$this->template->del('tool');
		$basic_property = $this->template->cloneRegion('basic_property');
		$this->template->del('basic_property');

		$tools = $this->app->getFrontEndTools();
		
		$bs_view=$this->add('xepan\cms\View_CssOptions',null,'basic_property');
		$this->add('xepan\cms\View_EditorTopBar',null,'topbar_editor');
		
		$tab_toolbar = $this->add('Tabs',null,'groups');

		foreach (array_keys($tools) as $group) {
			$tab = $tab_toolbar->addTab($group);

			$g_v = $tab->add('View',null,null,clone $group_tpl);

			foreach ($tools[$group] as $tool) {
				$t_v = $g_v->add($tool,null,'tools');
				$t_v->getOptionPanel($this,'tool_options');
				$t_v_icon = $tab->add('View',null,null,clone $tool_tpl);
				$tool_arr = explode("\\", $tool);
				$tool_name = array_pop($tool_arr);
				$tool_name = str_replace("Tool_", '', $tool_name);
				$t_v_icon->template->set('name',$tool_name);
				$t_v_icon->template->set('namespace',implode("/", $tool_arr));
				$t_v_icon->js(true)->xepanTool(
					[
					'name'=>$tool,
					'drop_html'=> $t_v->runatServer ? '<div class="xepan-component xepan-serverside-component" xepan-component-name="'.$tool_name.'" xepan-component="'.str_replace('\\', '/', get_class($t_v)).'">' .$t_v->getHTML(). '</div>': $t_v->getHTML()
					]
				);
			}

			$g_v->template->del('tools');
		}
		
		// ========= Widget Tools from Template =========
		$tab = $tab_toolbar->addTab("Template Widgets");
		$g_v = $tab->add('View',null,null,clone $group_tpl);
		// $g_v->template->set('name','Template Widgets');
		$path = 'widget/';

		if(file_exists(realpath("./websites/".$this->app->epan['name']."/www/".$path))){
			foreach (new \DirectoryIterator("./websites/".$this->app->epan['name']."/www/".$path) as $fileInfo) {
				$file_name=$fileInfo->getFilename();
				$temp_array=explode('.',$file_name);
				if($temp_array[1]!="html")continue;
				$subject = $temp_array[0];
				$pattern = "/_options/";
				preg_match($pattern,$subject, $matches, PREG_OFFSET_CAPTURE);
				if($matches)continue;
			    if($fileInfo->isDot()) continue;
			    $file =  $path.$fileInfo->getFilename();
				$temp=str_replace('.html',"",$file);
			    $t_v=$tab->add('xepan\cms\View_Tool',['runatServer'=>false],null,[$temp]);
				$this->add('View',null,'tool_options',[strtolower($temp).'_options']);
				$wt=explode("/",$temp);
				$image_url='./websites/'.$this->app->epan['name'].'/www/widget/'.$wt[1].'_icon.png';
				$t_v_icon = $g_v->add('View',null,'tools')->setHtml('<img src="'.$image_url.'" onerror="this.src=\'./vendor/xepan/cms/templates/images/default_icon.png\'" /><br/>'.$wt[1])->addClass(' col-md-4 col-sm-4 col-xs-4 text-center');
				$t_v_icon->js(true)->xepanTool(
						[
						'name'=>$tool,
						'drop_html'=> $t_v->getHTML()
						]
					);
			}
			$g_v->template->del('tools');
		}

		$this->js(true)
			->_load($this->api->url()->absolute()->getBaseURL().'vendor/xepan/cms/templates/js/xepanComponent.js')
			->_load($this->api->url()->absolute()->getBaseURL().'vendor/xepan/cms/templates/js/xepanEditor.js')
			->insertAfter('body')
			->xepanEditor([
				'base_url'=>$this->api->url()->absolute()->getBaseURL(),
				'file_path'=>$this->app->page_object instanceof \xepan\cms\page_cms?realpath($this->app->page_object->template->origin_filename):'false',
				'template_file'=>$this->app->page_object instanceof \xepan\cms\page_cms?realpath($this->app->template->origin_filename):'false',
				'template'=>$this->app->page_object instanceof \xepan\cms\page_cms?$this->app->template->template_file:'false',
				'save_url'=> $this->api->url()->absolute()->getBaseURL().'?page=xepan/cms/admin/save_page&cut_page=1',
				'template_editing'=> isset($_GET['xepan-template-edit'])
			])->_selector('.xepan-toolbar');
		
		$this->js(true)->insertAfter('body')->_selector('.xepan-tools-options');
		$this->js(true)->insertAfter('body')->_selector('.xepan-cms-toolbar');
		$this->js(true)->insertAfter('body')->_selector('.epan-editor-top-panel');
	
		if($this->app->editing_template){
			$this->js(true)->_selector('body')->addClass('xepan-component xepan-sortable-component');
			$this->js(true)->xepanComponent(['editing_template'=>$this->app->editing_template])->_selector('body.xepan-component, body .xepan-component');
			$this->js(true)->_selector('.xepan-page-wrapper')->removeClass('xepan-component xepan-sortable-component');
		}else
			$this->js(true)->xepanComponent()->_selector('body .xepan-component');
		// $this->js(true)->resizable()->_selector('.xepan-toolbar');
		
	}

	function render(){
		
		parent::render();
	}

	function defaultTemplate(){
		return ['view/cms/toolbar/layout'];
	}
}
