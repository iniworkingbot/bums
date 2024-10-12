<?php
error_reporting(0);
$list_query = array_filter(@explode("\n", str_replace(array("\r", " "), "", @file_get_contents(readline("[?] List Query       ")))));
$reff = readline("[?] Referral      ");
echo "[*] Total Query : ".count($list_query)."\n";
for ($i = 0; $i < count($list_query); $i++) {
    $c = $i + 1;
    echo "\n[$c]\n";
    if(empty($reff)){
        $auth = get_auth($list_query[$i]);
    }
    else{
        $auth = get_auth($list_query[$i], $reff);
    }
    echo "[*] Get Auth : ";
    if($auth){
        echo "success\n";
        $task = get_task($auth);
        echo "[*] Get Task : ";
        if($task){
            echo "success\n\n";
            for ($a = 0; $a < count($task); $a++) {
                $ex = explode("|", $task[$a]);
                echo "[-] ".$ex[1]." : ".solve_task($ex[0], $auth)."\n";
            }
        }
    }
}



function get_auth($query, $reff = false){
    if($reff){
        $data = [
            'invitationCode' => $reff,
            'initData' => $query,
        ];
        $curl = curl("login", "user/telegram_auth", false, $data)['data']['token'];
    }
    else{
        $data = [
            'invitationCode' => '',
            'initData' => $query,
        ];
        $curl = curl("login", "user/telegram_auth", false, $data)['data']['token'];
    }
    return $curl;
}

function get_task($auth){
    $curl = curl("task", "task/lists", $auth)['data']['lists'];
    for ($j = 0; $j < count($curl); $j++) {
        $list[] = $curl[$j]['id']."|".$curl[$j]['name'];
    }
    return $list;
}

function solve_task($id, $auth){
    $curl = curl("task", "task/finish_task", $auth, "id=$id")['msg'];
    return $curl;
}

function curl($type, $path, $auth = false, $body = false){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.bums.bot/miniapps/api/'.$path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($body){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $headers = array();
    $headers[] = 'Host: api.bums.bot';
    $headers[] = 'Accept-Language: en';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36';
    $headers[] = 'Accept: application/json, text/plain, */*';
    if($type == "login"){
        $headers[] = 'Content-Type: multipart/form-data';
    }
    else{
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
    }
    if($auth){
        $headers[] = 'Authorization: Bearer '.$auth;
    }
    $headers[] = 'Origin: https://app.bums.bot';
    $headers[] = 'Referer: https://app.bums.bot/';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $decode = json_decode($result, true);
    return $decode;
}