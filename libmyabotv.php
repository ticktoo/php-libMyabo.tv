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
		
		function enumerateStations()	{
			$s = array(
				"233" => "2PLUS2",
				"242" => "3PLUS",
				"1" => "3SAT",
				"58" => "ANIXE",
				"2" => "ARD",
				"4" => "ARD EINSFESTIVAL",
				"5" => "ARD EINSPLUS",
				"6" => "ARTE",
				"53" => "ARTEFR",
				"215" => "ATV",
				"7" => "BAY3",
				"9" => "BBC WORLD",
				"48" => "BIBELTV",
				"8" => "BR ALPHA",
				"10" => "CNBC",
				"11" => "CNN",
				"47" => "COMEDYCENTRAL",
				"45" => "DAS4",
				"49" => "DELUXEMUSIC",
				"38" => "DMAX",
				"60" => "ERF",
				"12" => "EURONEWS",
				"13" => "EUROSPORT",
				"14" => "GOTV",
				"15" => "HR3",
				"50" => "IMUSIC",
				"229" => "ITALIA1",
				"16" => "KABEL 1",
				"17" => "KIKA",
				"56" => "KTV",
				"216" => "M6",
				"18" => "MDR",
				"21" => "N24",
				"22" => "NDR",
				"44" => "NICKELODEON",
				"20" => "NTV",
				"193" => "ORF1",
				"194" => "ORF2",
				"218" => "ORF3",
				"219" => "ORFSPORTPLUS",
				"195" => "PERWY",
				"24" => "PHOENIX",
				"25" => "PRO7",
				"198" => "PULS4",
				"227" => "RAI1",
				"228" => "RAI2",
				"231" => "RAI3",
				"26" => "RBB",
				"250" => "RIC",
				"27" => "RTL",
				"28" => "RTL2",
				"230" => "RTLNITRO",
				"196" => "RTRPLANETA",
				"29" => "SAT1",
				"252" => "SAT1GOLD",
				"243" => "SF1",
				"244" => "SF2",
				"172" => "SIXX",
				"128" => "SPORT1",
				"30" => "SRTL",
				"63" => "STV",
				"31" => "SW3",
				"245" => "TAGESSCHAU24",
				"32" => "TELE5",
				"213" => "TF1",
				"246" => "TRT",
				"34" => "TV5",
				"55" => "TVPHISTORIA",
				"61" => "TVPINFO",
				"54" => "TVPKULTURA",
				"51" => "TVPOLONIA",
				"138" => "UKBBC",
				"173" => "UKBBC2",
				"155" => "UKBBC3",
				"174" => "UKBBC4",
				"214" => "UKCBBC",
				"253" => "UKCBEEBIES",
				"165" => "UKCBSACTION",
				"197" => "UKCBSDRAMA",
				"169" => "UKCHANNEL4",
				"163" => "UKE4",
				"162" => "UKFILM4",
				"164" => "UKFIVE",
				"199" => "UKHORROR",
				"157" => "UKITV",
				"158" => "UKITV2",
				"159" => "UKITV3",
				"175" => "UKITV4",
				"212" => "UKMORE4",
				"232" => "USBOUNCETV",
				"251" => "USIONTV",
				"254" => "USNJTV",
				"255" => "USWABC",
				"66" => "USWCBS",
				"75" => "USWFUT",
				"68" => "USWNBC",
				"71" => "USWNJU",
				"74" => "USWNYE",
				"67" => "USWNYW",
				"249" => "USWPIX",
				"69" => "USWWOR",
				"76" => "USWXTV",
				"35" => "VIVA",
				"36" => "VOX",
				"217" => "W9",
				"37" => "WDR",
				"39" => "ZDF",
				"41" => "ZDF INFO",
				"180" => "ZDFKULTUR",
				"64" => "ZDF NEO",			
			);
			
			return($s);
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
		
		function search($term, $station = '', $cut = false)	{
			$term = urlencode($term);
			
			if((int)$station == 0)	{
				$station = $this->resolveStationName($station);
			}
			if($cut)	{
				$c = "&cut=on";
			}
			
			$ret = false;
			$page = 1;
			
			while(1 == 1)	{
				$url 	 = "http://myabo.tv/search/?page=$page&order=startdate&order_type=ASC&term=$term&download=on&date=&genre=&station=$station&search_languages=1&quality=0".$c."&timerange=";
				echo("# $url\n");
				$request = "GET $url HTTP/1.0\n";
				$request.= "Host: myabo.tv\n";
				$request.= "Cookie: sessionid=".$this->authToken."\n\n";
		
				$response = "";
				$sh = fsockopen("myabo.tv", "80");	
				fputs($sh, $request);
				while($buffer = fgets($sh, 1024))	{
					$response.= $buffer;
				}
				fclose($sh);
			
				$tmp = array();
				$pattern = "|<a  class=\"dl\" href=\"(.*?)\" style=\"display:inline;\">Download</a>|i";
				preg_match_all($pattern, $response, $tmp);
				
				if(count($tmp[1]) > 0)	{
					foreach((array)$tmp[1] as $index=>$url)	{
						$ret[] = new MyAboTvShow($this->authToken, $url);
					}
					$page++;
				} else {
					break;
				}
			}
			return($ret);			
		}
		
		private function resolveStationName($stationname)	{
			$stationname = strtolower($stationname);
			$stations	 = $this->enumerateStations();
			foreach($stations as $id=>$station)	{
				if($stationname == strtolower($station))	{
					return($id);
				} 
			}
			return(false);
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
				
			// Date
				if(isset($this->files[0]) && isset($this->files[0]->url))	{
					$fn = $this->files[0]->url;
					$pattern = "|.*_([0-9][0-9]\.[0-9][0-9]\.[0-9][0-9]_[0-9][0-9]-[0-9][0-9])_.*|i";
					preg_match($pattern, $fn, $tmp5);
						$tmp6 = explode("_", $tmp5[1]);
						$d = explode(".", $tmp6[0]);
						$t = explode("-", $tmp6[1]);
					
						$ts = mktime($t[0], $t[1], 0, $d[1], $d[2], (int)$d[0] +2000);
						$this->timestamp = $ts;
						$this->datetime = date("d.m.Y H:i", $ts);
				} else {
					print_r($this);
					die("--\n");
				}
				
	
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
