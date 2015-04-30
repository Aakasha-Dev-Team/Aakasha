<?php namespace core;
use helpers\session as Session,
	helpers\globals as Globals,
	helpers\url as Url;
/*
 * View - load template pages
 *
 * @author David Carr - dave@daveismyname.com - http://www.daveismyname.com
 * @version 2.1
 * @date June 27, 2014
 */
class View {
	
    //here will be stored varables
    private $vars = [];

	/**
	 * include template file
	 * @param  string  $path  path to file from views folder
	 * @param  array $data  array of data
	 * @param  array $error array of errors
	 */
	public function loadPage($path) {
		//template path
		$templatePath  = APP_PATH . "views/" . Session::get('template');
		
		//data
		$data = $this->vars;
		
		//parser
		$parser = Globals::get("parser");
		
		//header
		$this->loadTemplate("header", $data);
		
		//view
		$view = $parser->parse($path, $data, ["path" => $templatePath]);
		
		//footer
		$this->loadTemplate("footer", $data);
	}

	/**
	 * return absolute path to selected template directory
	 * @updated Bobi on 22 Nov 2014 :: $keyMustHave 
	 * @param  string  $path  path to file from views folder
	 * @param  array $data  array of data
	 */
	private function loadTemplate($path, $data = NULL) {
		//template name
		$template = Session::get('template');
		
		//required vars
		$keyMustHave   = ["js", "jq", "title"];
		
		foreach ($keyMustHave as $name) {
			if (!array_key_exists($name, $data)) {
				$data[$name] = "";
			}
		}
		
		//vars
		$vars = $this->getTemplateVars("template");
		$data["head_title"]    = $data['title'] . ' - ' . SITETITLE;
		$data["path_template"] = Url::get_template_path();
		
		foreach($vars as $key => $value) {
			$data[$key] = $value;
		}
		
		//parser
		$parser = Globals::get("parser");
		
		//template path
		$templatePath  = APP_PATH . "templates/" . Session::get('template');
		
		//template
		$parser->parse($path, $data, ["path" => $templatePath]);
	}
	
	/**
	 * Add content to page
	 * @author Bobi <me@borislazarov.com> on 26 Nov 2014
	 * @return void
	 */
	public function addContent($name, $value = NULL) {
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->vars[$key] = $value;
			}
		} else {
			$this->vars[$name] = $value;
		}
	}
	
	/**
	 * Get template vars from app/etc/templates.php
	 * @author Bobi <me@borislazarov.com> on 27 Nov 2014
	 * @return array
	 */
    public function getTemplateVars($name) {
        $templateVars = Globals::get('template_vars');
        return $templateVars[Session::get('template')][$name];
    }
	
}