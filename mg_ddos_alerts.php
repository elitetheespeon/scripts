<?php
//Webhook URL
$webhookurl = "https://canary.discord.com/api/webhooks/xxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

//Check for start of attack
if (strpos($_REQUEST['subject'], 'Detection of an attack')) {
    if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $_REQUEST['subject'], $ip_match)) {
       $body = ":red_circle: DDoS attack mitigated on IP address: **{$ip_match[0]}**\r\n\r\nAn attack was detected and automatically mitigated on this IP address. Minor lag may happen.";
    }
}

//Check for end of attack
if (strpos($_REQUEST['subject'], 'End of attack')) {
    if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $_REQUEST['subject'], $ip_match)) {
       $body = ":green_circle: DDoS attack ended on IP address: **{$ip_match[0]}**\r\n\r\nThe traffic on this IP address has returned to normal and the attack is considered over.";
    }
}

//Get current time
$timestamp = date("c", strtotime("now"));

//Encode webhook data
$json_data = json_encode([
    //Message
    "content" => "",
    
    //Username
    "username" => "DDoS Alerts",

    //Text-to-speech
    "tts" => false,

    //Embed
    "embeds" => [
        [
            //Embed Title
            "title" => ":shield: DDoS Alert :shield:",

            //Embed Type
            "type" => "rich",

            //Embed Description
            "description" => $body,

            //Timestamp [ISO8601]
            "timestamp" => $timestamp,
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

//Send webhook to Discord
$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec( $ch );
curl_close( $ch );
?>
