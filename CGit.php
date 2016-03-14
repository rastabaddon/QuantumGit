<?php
	namespace Quantum\QGit;
/**************************************************************
 * Author
 * 
 * 
 */ 	
	class CGit
	{
			protected static $git_bin_path = "/usr/bin/git";
			protected static $git_bin_user = "apache";
		
			public static function run($path,$cmd,$args="")
			{
				try {
					$args = func_get_args();
					$path = array_shift($args); 
					$cmd = array_shift($args);
					$args = join(" ",$args);
					$todo = "/usr/bin/git --git-dir=".$path."/ ".$cmd." ".$args;
					
					if(\Quantum\QGit\CGit::$git_bin_user) { $todo = "sudo -u  ".\Quantum\QGit\CGit::$git_bin_user." -H  ".$todo; }
					 
					$return = shell_exec($todo);

					
				} catch(\Exception $e) {
					return $e->getMessage();
				}
				
				return $return;
			}
			
			/**
			 * @return \Quantum\QGit\CGitRepositiory
			 */
			public static function open($path) 
			{
				if(file_exists($path)) return new \Quantum\QGit\CGitRepositiory($path);
				return NULL;
			}
			
			
	}

/***
 * 	if(!file_exists($REPO_PATH))
	{
		shell_exec("sudo /usr/bin/git clone --depth 1 gitolite@sb.spectrum-industrial.co.uk:KIOSK-Image & ");
	} 
	
 */
	class CGitRepositiory
	{
		protected $name;
		protected $path;
		protected $gitPath;
		
		/**
		 * @var CCGitCommit
		 */
		protected $lastCommit; 
		
		public function __construct($path)
		{
			$this->path = $path;
			$this->gitPath = $path."/.git";
			$this->name = basename($this->path);
			
			//Last commit
			/*
			 *1	=>	04d8656bdbc0f587f25b0c9a55f11b7e3a4bf6fc
				2	=>	04d8656
				3	=>	Rafal Klimaszewski
				4	=>	rafal@centurion-europe.co.uk
				5	=>	040316
				6	=>	2016-03-04 09:02:00 +0000
			 */
			$this->_refresh();

		}
		
		private function _refresh()
		{
			 preg_match("/\[(.{40})\]\|\[(.{7})\]\|\[([\w\s]+)\]\|\[([\w\@\-\+\.]+)\]\|\[(.+)\]\|\[(.+)\]/",  \Quantum\QGit\CGit::run($this->gitPath,"log","--pretty=format:'[%H]|[%h]|[%cn]|[%ce]|[%s]|[%ci]'"), $output);
			if($output[1]) { $this->lastCommit = new \Quantum\QGit\CCGitCommit($this,$output[1]); } else { $this->lastCommit = NULL; }			
		}
		
		public function getGitPath() { return $this->gitPath; }
		
		public function getName() { return $this->name; }
		
		/**
		 * @return \Quantum\QGit\CCGitCommit
		 */
		public function getLastComit() { return $this->lastCommit; }
		
		public function getDescription() {
			 if(!file_exists($this->gitPath."/description")) return "";
			  return file_get_contents($this->gitPath."/description"); 
		}
		

		public function status()
		{
			$msg = \Quantum\QGit\CGit::run($this->gitPath,"status");
			$this->_refresh();
			return $msg;
		}
				
		public function pull()
		{
			$msg = \Quantum\QGit\CGit::run($this->gitPath,"pull");
			$this->_refresh();
			return $msg;

		}
	}
	
	class CCGitCommit
	{
			protected $hash;
			protected $shortHash;
			protected $author;
			protected $email;
			protected $message;
			protected $date;	

			/**
			 * @var \Quantum\QGit\CGitRepositiory 
			 */
			 protected $repositiory;
			 
			public function __construct(\Quantum\QGit\CGitRepositiory $repositiory,$commitID)
			{
					$this->repositiory = $repositiory;
					
					preg_match("/\[(.{40})\]\|\[(.{7})\]\|\[([\w\s]+)\]\|\[([\w\@\-\+\.]+)\]\|\[(.+)\]\|\[(.+)\]/",  \Quantum\QGit\CGit::run($this->repositiory->getGitPath(),"log","--pretty=format:'[%H]|[%h]|[%cn]|[%ce]|[%s]|[%ci]' ",$commitID), $output);
			
					$this->hash = $output[1];
					$this->shortHash = $output[2];
					$this->author = $output[3];
					$this->email = $output[4];
					$this->message = $output[5];
					$this->date = new \DateTime($output[6]);		
			}
	
			/***
			 * @return \DateTime
			 */
			public function getDate()
			{
				return $this->date;
			}
					
			public function getVersion()
			{
				return $this->shortHash;
			}

			public function getHash()
			{
				return $this->hash;
			}
					
			public function getMessage()
			{
				return $this->message;
			}
						
			public function getAuthor()
			{
				return $this->author;
			}
			
			public function getEmail()
			{
				return $this->email;
			}
	}
