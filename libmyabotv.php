<?PHP
	class MyAboTV	{
		var $authToken	= 0;
		function authenticate($username, $password)	{
			$this->username = $username;
			$this->password = $password;
			
			// 1. Acquire valid Au	// 1. Acquire valid Auth token
				$postdata = "username=$username&password=$password&login=";
				$request = "POST /login/ HTTP/1.0\n";
				$request.= "Host: myabo.tv\n";
				$request.= "Content-Type: application/x-www-form-urlencoded\n";
				$request.= "Content-Length: ".strlen($postdata)."\n\n";
				$request.= $postdata."\n";
				$response = "";
				$sh = fsockopen("myabo.tv", "80");	
				fputs($sh, $request);
				while($buffer = fgets($sh, 1024))	{
					$response.= $buffer;
				}
				fclose($sh);
		
				$pattern = "/Set-Cookie: sessionid=(.*?);/i";
				preg_match($pattern, $response, $tmp);
				$cookie = $tmp[1];	
				
				if(strlen($cookie) < 20)	{
					return(false);
				} else {
					$this->authToken = $cookie;
					return(true);
				}
		}
		
		function enumerateGenres()	{
			$c = array(
				'1-sport'			=> "Sport",
				'2-news'			=> "Nachrichten",
				'3-educational'		=> "Wissen",
				'4-music'			=> "Musik",
				'5-entertainment'	=> "Unterhaltung",
				'6-copulation'		=> "Erotik",
				'7-movie'			=> "Film",
				'8-child-youth'		=> "Kinder",
				'9-other'			=> "Andere"
			);
			return($c);
		}
		
		function enumerateShowsByGenre($genre, $days = 7)	{
			$request = "GET /topshows-genre/$genre/$days/ HTTP/1.0\n";
			$request.= "Host: myabo.tv\n";
			$request.= "Cookie: sessionid=".$this->authToken."\n\n";
		
			$response = "";
			$sh = fsockopen("myabo.tv", "80");	
			fputs($sh, $request);
			while($buffer = fgets($sh, 1024))	{
				$response.= $buffer;
			}
			fclose($sh);
			
			$pattern = "|<h3><a href=\"(.*?)\" title=\"(.*?)\" class=\".*\">|i";
			preg_match_all($pattern, $response, $tmp);
			
			foreach($tmp[1] as $index=>$url)	{
				$ret[] = new MyAboTvShow($this->authToken, $url);
			}
			return($ret);
		}
	}
	
	class MyAboTvShow	{
		function __construct($authToken, $url)	{
			$this->authToken = $authToken;
		
			$request = "GET $url HTTP/1.0\n";
			$request.= "Host: myabo.tv\n";
			$request.= "Cookie: sessionid=".$this->authToken."\n\n";
		
			$response = "";
			$sh = fsockopen("myabo.tv", "80");	
			fputs($sh, $request);
			while($buffer = fgets($sh, 1024))	{
				$response.= $buffer;
			}		
			
			// title
				$pattern = "|<h2>(.*?)</h2>|i";
				preg_match($pattern, $response, $tmp1);
				$this->title = trim(($tmp1[1]));
				
				// filename compatible title
				$allowed = "abcdefghijklmnopqrstuvwxyzäöüßABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_- ";
				$ret = "";
				for ($c = 0; $c <= strlen($this->title)-1; $c++)	{
					$char = substr($this->title, $c, 1);
			
					if(strpos($allowed, $char) === FALSE)	{
						$ret.= " ";
					} else {
						$ret.= $char;
					}
				}	
				$this->safeTitle = $ret;
			
				
				
			// sender
				$pattern = "|<img src=\"/images/tv_channel_logos/.*?\" alt=\"(.*?)\"/>|i";
				preg_match($pattern, $response, $tmp2);
				$this->station = trim($tmp2[1]);
				
			// Plot
				$pattern = "|<p style=\"color: #777;font-size: 12px;line-height: 14px;\">(.*?)</p>|isu";
				preg_match($pattern, $response, $tmp3);
				$this->plot = trim($tmp3[1]);
				
			// Files
				$pattern = "|<tr>(.*?)class=\"download\"(.*?)</tr>|ius";
				preg_match_all($pattern, $response, $tmp4);
				
#				print_r($tmp4);
#				die();
				
				foreach($tmp4[2] as $html)	{
					$f = new MyAboTvFile($this->authToken);
					
					$f->initByHtml($html);
					$this->files[] = $f;
				}
				
			// sort files by score	
				$hashtable = array();
				foreach($this->files as $index=>$item)	{
					$hashtable[$index] = $item->score;
				
				}
				arsort($hashtable);
				foreach($hashtable as $index=>$key)	{
					$out[] = $this->files[$index];
				}	
				$this->files = $out;			
		}
	}
	
	class MyAboTvFile	{
		private $authToken = "";
		function __construct($authToken)	{
			$this->authToken = $authToken;
		}
		
		function initByHtml($html)	{
			$props = array();

			$patterns['quality']	= array(
				'MQ'	=> '|<img src="/images/avi.gif"|is',
				'LQ'	=> '|<img src="/images/mp4.gif"|is',
				'HQ'	=> '|<img src="/images/HQ.avi.gif"|is',
				'HD'	=> '|<img src="/images/HD.avi.gif"|is',
				'HQC'	=> '|<img src="/images/HQ.cut.mp4.gif"|is',
				'LQC'	=> '|<img src="/images/cut.mp4.gif"|is',
				'S'		=> '|<img src="/images/flv.gif"|is'
			);
			$scores					= array(
				'S'		=> 0,
				'LQ'	=> 1,
				'MQ'	=> 3,
				'HQ'	=> 8,
				'HD'	=> 4
			);
			$patterns['cut']		= '|<img src="/images/icons/cut.png"|is';


			foreach($patterns['quality'] as $Q=>$pattern)	{
				if (preg_match($pattern, $html))	{
					# echo("# Match: $pattern [$Q]\n");
					$props['QUALITY'] = $Q;
					break;
				}
			}

			if(strlen($props['QUALITY']) == 3)	{
				$props['QUALITY'] = substr($props['QUALITY'], 0, 2);
				$props['CUT'] = 2;
			} else {
				if(preg_match($patterns['cut'], $html))	{
					$props['CUT'] = 2;
				} else {
					$props['CUT'] = 1;
				}
			}


			// assigning scores.
			$props['SCORE'] = $scores[$props['QUALITY']] * ($props['CUT']+1);
			
			foreach($props as $key=>$value)	{
				$key = strtolower($key);
				$this->$key = $value;
			}
			
			$this->cut--;
			
			if($this->quality != "S")	{
				$pattern = '|<a class="download" href="(.*?)" target="downloadframe">|is';
		
				preg_match($pattern, $html, $tmp6);
				$url = $tmp6[1];
				if(strpos($url, "?") !== FALSE)	{
					$tmp = explode("?", $url);
					$url = $tmp[0];
				
					$this->url = str_replace("/de-de/", "/de/", $url);
					
					$this->extension = $this->getExtension($url);	
				}
			}
		}
		
		private function getExtension($url)	{
			$pattern = "/.*\\.(.*)\$/i";
			preg_match($pattern, $url, $tmp);
			return($tmp[1]);	
		}
	}
	
 ?>
