<?php
class Checker{
	//Config
	const NIDS_SENSOR = 'your-sensor-name';
	const HIDS_SENSOR = 'alienvault';
	const AGENT_NAME = 'your-sensor-name';
	const AGENT_IP = '1.1.1.1';
	const WEBHOOK_URL = 'https://discord.com/api/webhooks/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	const FORENSICS_URL = 'https://youralienvaulturl.com/ossim/forensics/';
	const NIDS_COOKIE = 'sess=xxxxxxxxxxxxxxxxxxxx; PHPSESSID=xxxxxxxxxxxxxxxxxxxxxxxxxx';
	const HIDS_COOKIE = 'sess=xxxxxxxxxxxxxxxxxxxx; PHPSESSID=xxxxxxxxxxxxxxxxxxxxxxxxxx';

    //Define F3 instance
    var $f3;

    //Define Guzzle client instance
    var $client;

    //Set up handles
    function __construct($f3){
        $this->f3 = $f3;
        $this->client = new GuzzleHttp\Client([
			'verify' => false,
    		'proxy' => 'yourproxy:port',
    		'curl' => [CURLOPT_PROXYTYPE => 7],
		]);;
    }

	//Send Webhook for NIDS
	function send_webhook_nids($date, $event, $source_ip, $source_port, $destination_ip, $destination_port, $risk){
		$timestamp = date("c", $date);
		
		switch($risk){
			case 'low': 
				$risk_color = "92d100";
				break;
			case 'med':
				$risk_color = "ff8a00";
				break;
			case 'high':
				$risk_color = "fd0000";
				break;
			default: 
				$risk_color = "92d100";
				break;
		}
		
		$source_ip = str_replace(self::AGENT_NAME, self::AGENT_IP, $source_ip);
		$destination_ip = str_replace(self::AGENT_NAME, self::AGENT_IP, $destination_ip);
		$source = $source_ip.":".$source_port;
		$destination = $destination_ip.":".$destination_port;
		$risk_upper = strtoupper($risk);
		

		$json_data = json_encode([
			// Username
			"username" => "Network Intrusion Detection",
			
			// Avatar
			"avatar_url" => "https://avatars.githubusercontent.com/u/25514665?s=200&v=4",

			// Embeds Array
			"embeds" => [
				[
					// Embed Title
					"title" => "[NIDS logged event] - Risk $risk_upper",

					// Embed Description
					"description" => "$event",
					
					// Embed Color
					"color" => hexdec($risk_color),
					
					// Embed Fields
					"fields" => [
						[
							"name" => "Source Host",
							"value" => "$source"
						], [
							"name" => "Destination Host",
							"value" => "$destination"
						]
					],

					// Timestamp of embed must be formatted as ISO8601
					"timestamp" => $timestamp,
				]
			]

		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


		$ch = curl_init( self::WEBHOOK_URL );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
		$fp = fopen('log.txt', 'a');
		fwrite($fp, dump($response)."\r\n");
		fwrite($fp, dump($json_data)."\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);
		curl_close( $ch );
	}
	
	//Send Webhook for HIDS
	function send_webhook_hids($date, $event, $source_ip, $destination_ip, $risk, $file){
		$timestamp = date("c", $date);
		
		switch($risk){
			case 'low': 
				$risk_color = "92d100";
				break;
			case 'med':
				$risk_color = "ff8a00";
				break;
			case 'high':
				$risk_color = "fd0000";
				break;
			default: 
				$risk_color = "92d100";
				break;
		}
		
		$source_ip = str_replace(self::AGENT_NAME, self::AGENT_IP, $source_ip);
		$destination_ip = str_replace(self::AGENT_NAME, self::AGENT_IP, $destination_ip);
		$source = $source_ip;
		$destination = $destination_ip;
		$risk_upper = strtoupper($risk);
		

		$json_data = json_encode([
			// Username
			"username" => "Host Intrusion Detection",
			
			// Avatar
			"avatar_url" => "https://avatars.githubusercontent.com/u/25514665?s=200&v=4",

			// Embeds Array
			"embeds" => [
				[
					// Embed Title
					"title" => "[HIDS logged event] - Risk $risk_upper",

					// Embed Description
					"description" => "$event",
					
					// Embed Color
					"color" => hexdec($risk_color),
					
					// Embed Fields
					"fields" => [
						[
							"name" => "Source Host",
							"value" => "$source"
						]
						,[
							"name" => "File",
							"value" => "$file"
						]
					],

					// Timestamp of embed must be formatted as ISO8601
					"timestamp" => $timestamp,
				]
			]

		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


		$ch = curl_init( self::WEBHOOK_URL );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
		$fp = fopen('log.txt', 'a');
		fwrite($fp, dump($response)."\r\n");
		fwrite($fp, dump($json_data)."\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);
		curl_close( $ch );
	}

    //Parse NIDS
    function parse_nids(){
		//Check last run
		$last_run = file_get_contents("last_nids.txt");
		$last_run_conv = strtotime($last_run);
		echo "[NIDS] Last Run: ($last_run_conv)\r\n";
		$fp = fopen('log.txt', 'a');
		fwrite($fp, "[NIDS] Last Run: ($last_run_conv)\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);

		//Insert default headers
        $options = [
            'form_params' => [
            	'search' => '1',
				'gbhide' => '1',
				'mode' => '',
				'sensor' => '',
				'time[0][0]' => '+',
				'time[0][1]' => '>=',
				'time[0][2]' => date('m', $last_run_conv),
				'time[0][3]' => date('d', $last_run_conv),
				'time[0][4]' => date('Y', $last_run_conv),
				'time[0][5]' => date('H', $last_run_conv),
				'time[0][6]' => date('i', $last_run_conv),
				'time[0][7]' => date('s', $last_run_conv),
				'time[0][8]' => '+',
				'time[0][9]' => 'AND',
				'time[1][0]' => '+',
				'time[1][1]' => '+',
				'time[1][2]' => '+',
				'time[1][3]' => '',
				'time[1][4]' => '+',
				'time[1][5]' => '',
				'time[1][6]' => '',
				'time[1][7]' => '',
				'time[1][8]' => '+',
				'ossim_risk_a' => '+',
				'ossim_priority[0]' => '=',
				'ossim_priority[1]' => '',
				'ossim_asset_dst[0]' => '=',
				'ossim_asset_dst[1]' => '',
				'ossim_reliability[0]' => '=',
				'ossim_reliability[1]' => '',
				'ip_addr[0][0]' => '+',
				'ip_addr[0][1]' => '+',
				'ip_addr[0][2]' => '=',
				'ip_addr[0][3]' => '',
				'ip_addr[0][8]' => '+',
				'data_encode[0]' => '+',
				'data_encode[1]' => '+',
				'data[0][0]' => '+',
				'data[0][1]' => '+',
				'data[0][2]' => '',
				'data[0][3]' => '+',
				'data[0][4]' => '+',
				'sourcetype' => '',
				'category[0]' => '',
				'category[1]' => '',
				'new' => '1',
				'submit' => 'Query+DB'
			],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; en-GB; rv:118.0esr) Gecko/20070104 Firefox/118.0esr',
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
				'DNT' => '1',
				'Connection' => 'keep-alive',
				'Referer' => self::FORENSICS_URL . 'base_qry_form.php',
				'Cookie:' => self::NIDS_COOKIE,
				'Upgrade-Insecure-Requests' => '1',
				'Sec-Fetch-Dest' => 'iframe',
				'Sec-Fetch-Mode' => 'navigate',
				'Sec-Fetch-Site' => 'same-origin',
				'Sec-Fetch-User' => '?1',
				'Pragma' => 'no-cache',
				'Cache-Control' => 'no-cache'
            ],
        ];

        try{
            //Run HTTP request and return body
            $request = $this->client->request('POST', self::FORENSICS_URL . 'base_qry_main.php', $options);
            $body = (string)$request->getBody();
            dump($body);
        }catch(Exception $e){
        	echo "[NIDS] Error in request.\r\n";
			$fp = fopen('log.txt', 'a');
			fwrite($fp, "[NIDS] Error in request.\r\n");
			fwrite($fp, "\r\n");
			fclose($fp);
            return false;
        }

        //Verify HTML was returned
        if (strlen($body) == 0){
        	echo "[NIDS] Error validating request data.\r\n";
			$fp = fopen('log.txt', 'a');
			fwrite($fp, "[NIDS] Error validating request data.\r\n");
			fwrite($fp, "\r\n");
			fclose($fp);
            return false;
        }
		
        //Start HTML parser and load HTML in
		$dom = new PHPHtmlParser\Dom;
        $dom->load($body);
        
        //Select HTML objects that should be rows
        $rows = $dom->find('.table_list > tr');
        $i=0;
        
        echo "[NIDS] Returned Events: ".(count($rows))."\r\n";
		$fp = fopen('log.txt', 'a');
		fwrite($fp, "[NIDS] Returned Events: ".(count($rows))."\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);
        
        foreach ($rows as $row){
            if($i !== 0){
                $columns = $row->find('td');
                $event_name = trim(html_entity_decode($columns[1]->find('span')->text));
                $date = trim($columns[2]->text);
                $sensor = trim($columns[3]->find('a')->text);
                $otx = trim($columns[4]->text);
                $source_ip = trim($columns[5]->find('div > a')->text);
                $source_port = substr(trim($columns[5]->find('div')->text), 1);
                $destination_ip = trim($columns[6]->find('div > a')->innerHtml);
                if ($destination_ip == 'de01-proxy'){
                    $destination_port = substr(trim($columns[6]->find('div > b')->text), 1);
                }else{
                    $destination_port = substr(trim($columns[6]->find('div')->text), 1);
                }
				$risk = substr(trim($columns[8]->find('a > span')->text), 0, -4);
				
                $date_conv = strtotime($date);
                if ( $sensor == self::NIDS_SENSOR && $date_conv > $last_run_conv && ( $source_ip == self::AGENT_NAME || $destination_ip == self::AGENT_NAME ) ){
					echo "[NIDS] ($i)\r\n";
					echo "[NIDS] Webhook: ".self::WEBHOOK_URL."\r\n";
					echo "[NIDS] Event: ".($event_name)."\r\n";
					echo "[NIDS] Date: ".($date)."\r\n";
					echo "[NIDS] Sensor: ".($sensor)."\r\n";
					echo "[NIDS] Source IP: ".($source_ip)."\r\n";
				    echo "[NIDS] Source Port: ".($source_port)."\r\n";
					echo "[NIDS] Destination IP: ".($destination_ip)."\r\n";
					echo "[NIDS] Destination Port: ".($destination_port)."\r\n";
					echo "\r\n";
					
					$fp = fopen('log.txt', 'a');
					fwrite($fp, "[NIDS] ($i)\r\n");
					fwrite($fp, "[NIDS] Webhook: ".self::WEBHOOK_URL."\r\n");
					fwrite($fp, "[NIDS] Event: ".($event_name)."\r\n");
					fwrite($fp, "[NIDS] Date: ".($date)."\r\n");
					fwrite($fp, "[NIDS] Sensor: ".($sensor)."\r\n");
					fwrite($fp, "[NIDS] Source IP: ".($source_ip)."\r\n");
				    fwrite($fp, "[NIDS] Source Port: ".($source_port)."\r\n");
					fwrite($fp, "[NIDS] Destination IP: ".($destination_ip)."\r\n");
					fwrite($fp, "[NIDS] Destination Port: ".($destination_port)."\r\n");
					fwrite($fp, "\r\n");
					fclose($fp);
					
					//Send Webhook
					$this->send_webhook_nids($date_conv, $event_name, $source_ip, $source_port, $destination_ip, $destination_port, $risk);
                }
            }
            $i++;
        }
		//Save last run
		file_put_contents("last_nids.txt", date('Y-m-d H:i:s'), LOCK_EX);
    }
    
    //Parse HIDS
    function parse_hids(){
		//Check last run
		$last_run = file_get_contents("last_hids.txt");
		$last_run_conv = strtotime($last_run);
		echo "[HIDS] Last Run: ($last_run_conv)\r\n";
		$fp = fopen('log.txt', 'a');
		fwrite($fp, "[HIDS] Last Run: ($last_run_conv)\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);
		
		//Insert default headers
        $options = [
            'form_params' => [
            	'search' => '1',
				'gbhide' => '1',
				'mode' => '',
				'sensor' => '',
				'time[0][0]' => '+',
				'time[0][1]' => '>=',
				'time[0][2]' => date('m', $last_run_conv),
				'time[0][3]' => date('d', $last_run_conv),
				'time[0][4]' => date('Y', $last_run_conv),
				'time[0][5]' => date('H', $last_run_conv),
				'time[0][6]' => date('i', $last_run_conv),
				'time[0][7]' => date('s', $last_run_conv),
				'time[0][8]' => '+',
				'time[0][9]' => 'AND',
				'time[1][0]' => '+',
				'time[1][1]' => '+',
				'time[1][2]' => '+',
				'time[1][3]' => '',
				'time[1][4]' => '+',
				'time[1][5]' => '',
				'time[1][6]' => '',
				'time[1][7]' => '',
				'time[1][8]' => '+',
				'ossim_risk_a' => '+',
				'ossim_priority[0]' => '=',
				'ossim_priority[1]' => '',
				'ossim_asset_dst[0]' => '=',
				'ossim_asset_dst[1]' => '',
				'ossim_reliability[0]' => '=',
				'ossim_reliability[1]' => '',
				'ip_addr[0][0]' => '+',
				'ip_addr[0][1]' => '+',
				'ip_addr[0][2]' => '=',
				'ip_addr[0][3]' => '',
				'ip_addr[0][8]' => '+',
				'data_encode[0]' => '+',
				'data_encode[1]' => '+',
				'data[0][0]' => '+',
				'data[0][1]' => '+',
				'data[0][2]' => '',
				'data[0][3]' => '+',
				'data[0][4]' => '+',
				'sourcetype' => '',
				'category[0]' => '',
				'category[1]' => '',
				'new' => '1',
				'submit' => 'Query+DB'
			],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; en-GB; rv:118.0esr) Gecko/20070104 Firefox/118.0esr',
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
				'DNT' => '1',
				'Connection' => 'keep-alive',
				'Referer' => self::FORENSICS_URL . 'base_qry_form.php',
				'Cookie:' => self::HIDS_COOKIE,
				'Upgrade-Insecure-Requests' => '1',
				'Sec-Fetch-Dest' => 'iframe',
				'Sec-Fetch-Mode' => 'navigate',
				'Sec-Fetch-Site' => 'same-origin',
				'Sec-Fetch-User' => '?1',
				'Pragma' => 'no-cache',
				'Cache-Control' => 'no-cache'
            ],
        ];

        try{
            //Run HTTP request and return body
            $request = $this->client->request('POST', self::FORENSICS_URL . 'base_qry_main.php', $options);
            $body = (string)$request->getBody();
            dump($body);
        }catch(Exception $e){
        	echo "[HIDS] Error in request.\r\n";
			$fp = fopen('log.txt', 'a');
			fwrite($fp, "[HIDS] Error in request.\r\n");
			fwrite($fp, "\r\n");
			fclose($fp);
            return false;
        }

        //Verify HTML was returned
        if (strlen($body) == 0){
        	echo "[HIDS] Error validating request data.\r\n";
			$fp = fopen('log.txt', 'a');
			fwrite($fp, "[HIDS] Error validating request data.\r\n");
			fwrite($fp, "\r\n");
			fclose($fp);
            return false;
        }
		
        //Start HTML parser and load HTML in
		$dom = new PHPHtmlParser\Dom;
        $dom->load($body);
        
        //Select HTML objects that should be rows
        $rows = $dom->find('.table_list > tr');
        $i=0;
        
        echo "[HIDS] Returned Events: ".(count($rows))."\r\n";
		$fp = fopen('log.txt', 'a');
		fwrite($fp, "[HIDS] Returned Events: ".(count($rows))."\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);
        
        foreach ($rows as $row){
            if($i !== 0){
                $columns = $row->find('td');
                $event_name = trim(html_entity_decode($columns[1]->find('span')->text));
                $date = trim($columns[2]->text);
                $sensor = trim($columns[3]->find('a')->text);
                $otx = trim($columns[4]->text);
                $source_ip = trim($columns[5]->find('div > a')->text);
                $destination_ip = trim($columns[6]->find('div > a')->innerHtml);
				$risk = substr(trim($columns[8]->find('a > span')->text), 0, -4);
				$file = trim($columns[9]->text);
				
                $date_conv = strtotime($date);
				if ( $sensor == self::HIDS_SENSOR && $date_conv > $last_run_conv && ( $source_ip == self::AGENT_NAME || $destination_ip == self::AGENT_NAME ) && (!str_contains($file , '/run.sh')) && (!str_contains($file , '/server.jar')) && (!str_contains($file , '/versions/')) && (!str_contains($file , '/cache/')) && (!str_contains($file , '/paper-')) ){
					echo "[HIDS] ($i)\r\n";
					echo "[HIDS] Webhook: ".self::WEBHOOK_URL."\r\n";
					echo "[HIDS] Event: ".($event_name)."\r\n";
					echo "[HIDS] Date: ".($date)."\r\n";
					echo "[HIDS] Sensor: ".($sensor)."\r\n";
					echo "[HIDS] Source IP: ".($source_ip)."\r\n";
					echo "[HIDS] Risk: ".($risk)."\r\n";
					echo "[HIDS] File: ".($file)."\r\n";
					echo "\r\n";
					
					$fp = fopen('log.txt', 'a');
					fwrite($fp, "[HIDS] ($i)\r\n");
					fwrite($fp, "[HIDS] Webhook: ".self::WEBHOOK_URL."\r\n");
					fwrite($fp, "[HIDS] Event: ".($event_name)."\r\n");
					fwrite($fp, "[HIDS] Date: ".($date)."\r\n");
					fwrite($fp, "[HIDS] Sensor: ".($sensor)."\r\n");
					fwrite($fp, "[HIDS] Source IP: ".($source_ip)."\r\n");
					fwrite($fp, "[HIDS] Risk: ".($risk)."\r\n");
					fwrite($fp, "[HIDS] File: ".($file)."\r\n");
					fwrite($fp, "\r\n");
					fclose($fp);
					
					//Send Webhook
					$this->send_webhook_hids($date_conv, $event_name, $source_ip, $destination_ip, $risk, $file);
				}
            }
            $i++;
        }
		//Save last run
		file_put_contents("last_hids.txt", date('Y-m-d H:i:s'), LOCK_EX);
    }
}
