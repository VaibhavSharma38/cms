<?php


namespace xepan\cms;


class Controller_ServerSideComponentManager extends \AbstractController {
	public $spots= 1;
	function init(){
		parent::init();

		$this->createSpots();
		$this->renderServerSideComponents();
	}

	function createSpots(){
		// TODO :: Some caching ??		

		$this->pq = $pq = new phpQuery();
		$this->dom = $dom = $pq->newDocument($this->owner->template->template_source);

		if(!$this->owner instanceof \Frontend){
			$pq->pq($dom)->attr('xepan-page-content','true');
			$pq->pq($dom)->addClass('xepan-page-content');			
		}

		foreach($dom['.xepan-component'] as $d){
			$d=$pq->pq($d);
			if(!$d->hasClass('xepan-serverside-component')) continue;
			$i= $this->spots++;
			$inner_html = $d->html();
			$with_spot = '{'.$this->owner->template->name.'_'.$i.'}'. $inner_html.'{/}';
			$d->html($with_spot);
		}
		

		$content = $this->updateBaseHrefForTemplates();

	    $content = str_replace('<!--xEpan-ATK-Header-Start', '<!--xEpan-ATK-Header-Start-->', $content);
	    $content = str_replace('xEpan-ATK-Header-End-->', '<!--xEpan-ATK-Header-End-->', $content);
		$this->owner->template->loadTemplateFromString($content);

		$this->owner->template->trySet($this->app->page.'_active','active');
	}

	function renderServerSideComponents(){
		$dom = $this->dom;
		$this->spots=1;
		foreach($dom['.xepan-component'] as $d){
			$attributes=[];
			foreach ($d->attributes as $attr) {
				$attributes[$attr->name] = $attr->value;
			}
			$d=$this->pq->pq($d);
			if(!$d->hasClass('xepan-serverside-component')) continue;
			$i= $this->spots++;
			try{
				$this->owner->add($d->attr('xepan-component'),['_options'=>$attributes],$this->owner->template->name.'_'.$i);
			}catch(\Exception $e){
				if(!$this->app->isAjaxOutput()){
					$content =  method_exists($e,'getHTML')?$e->getHTML():$e->getMessage();
                	$this->owner->add('View',['_options'=>$attributes],$this->owner->template->name.'_'.$i)->setHTML($content);
				}else{
					throw $e;
				}
			}
		}
	}

	function updateBaseHrefForTemplates(){
		
		$dom = $this->dom;

		if($tp=$this->app->recall('xepan-template-preview',false)){
			$domain = $this->app->pm->base_url.$this->app->pm->base_path.'xepantemplates/'.$tp.'/';
			$rel_path = "";
		}
		else{
			$domain = $this->app->pm->base_url.$this->app->pm->base_path.'websites/'.$this->app->current_website_name.'/www';
			$rel_path = 'websites/'.$this->app->current_website_name.'/www/';
		}

		foreach ($dom['img']->not('[src^="http"]')->not('[src^="data:"]')->not('[src^="websites/'.$this->app->current_website_name.'"')->not('[src^="vendor/"]') as $img) {
			$img= $this->pq->pq($img);
			$img->attr('src',$rel_path.$img->attr('src'));
		}

		foreach ($dom['link']->not('[href^="http"]')->not('[src^="websites/'.$this->app->current_website_name.'/www/'.'"') as $img) {
			$img= $this->pq->pq($img);
			$img->attr('href',$rel_path.$img->attr('href'));
		}

		foreach ($dom['script[src]']->not('[src^="http"]')->not('[src^="//"]')->not('[src^="websites/'.$this->app->current_website_name.'/www/'.'"') as $img) {
			$img= $this->pq->pq($img);
			$img->attr('src',$rel_path.$img->attr('src'));
		}
		

		// $content = preg_replace("/(link.*|img.*|script.*)(href|src)\s*\=\s*[\"\']([^(http)])(\/)?/", "$1$2=\"$domain$3", $content);
		$content = preg_replace('/url\(\s*[\'"]?\/?(.+?)[\'"]?\s*\)/i', 'url('.$rel_path.'$1)', $dom->html());

		// $content = $dom->html();

		return $content;
	}
}