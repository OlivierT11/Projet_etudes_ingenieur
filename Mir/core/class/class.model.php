<?php
//Copyright RB Web Design @2017
//Controller class
class Model {
	private $page;
	private $display;
	public $error;
	
	//main void construct
	public function __construct() {
		$this->page="";
		$this->display="";
	}
	
	//MVC structure function to display asked page
	public function getPage($page) {
		$this->page=$page;
		if(isset($page) && strlen($page)>0) {
			if($page=="index.php") {
				$this->page="login";
			}
		} else {
			$this->getPage("index.php"); 
			return 0;
		}
		
		if(!file_exists(dirname(dirname(__FILE__))."/model/mod_".$this->page.".php")) //correction de la faille include. Le fichier DOIT exister au path spécifié.
		{
			include dirname(dirname(dirname(__FILE__)))."/www/404.php";
			exit;
		}
		
		ob_start();
		
		if(file_exists(dirname(dirname(__FILE__))."/model/mod_".$this->page.".php"))
		{
			include dirname(dirname(__FILE__))."/model/mod_".$this->page.".php";
		}
		if(file_exists(dirname(dirname(__FILE__))."/control/ctrl_".$this->page.".php"))
		{
			include dirname(dirname(__FILE__))."/control/ctrl_".$this->page.".php";
		}
		if(file_exists(dirname(dirname(__FILE__))."/view/vi_".$this->page.".php"))
		{
			include dirname(dirname(__FILE__))."/view/vi_".$this->page.".php";
		}
		
		$this->display=ob_get_clean();
		echo $this->display;
	}
}

?>
