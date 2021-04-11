<?php
/*
 +-----------------------------------------------------------------------------------------+
 | This file is part of the WHMCS module. "PUQ_WHMCS-Public-Synology"                      |
 | The module allows you to manage the Synology users as a product in the system WHMCS.    |
 | This program is free software: you can redistribute it and/or modify it                 |
 +-----------------------------------------------------------------------------------------+
 | Author: Ruslan Poloviy ruslan.polovyi@puq.pl                                            |
 | Warszawa 04.2021 PUQ sp. z o.o. www.puq.pl                                              |
 | version: 1.1                                                                            |
 +-----------------------------------------------------------------------------------------+
*/
function puqPublicSynology_MetaData()
{
   return array(
       'DisplayName' => 'PUQ Public Synology',
       'DefaultSSLPort' => '5001',
       'language' => 'english',
   );
}


function puqPublicSynology_ConfigOptions() {

    $configarray = array(
     'Group' => array( 'Type' => 'text', 'Default' => 'PublicSynology'),
     'User description'  => array( 'Type' => 'text', 'Default' => 'WHMCS'),
    );
    return $configarray;
}

function puqPublicSynology_AdminLink($params) {

    $code = '<form action="https://'.$params['serverhostname'].':'.$params['serverport'].'" method="post" target="_blank">
    <input type="submit" value="Login to Control Panel" />
    </form>';
    return $code;
}



function puqPublicSynology_apiCurl($params,$data,$url){

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    ## Login to API
    curl_setopt($curl, CURLOPT_URL, 'https://' . $params['serverhostname'] . ':' . $params['serverport'] . '/webapi/auth.cgi?api=SYNO.API.Auth&method=Login&version=3&format=sid&account=' . $params['serverusername'] . '&passwd=' . $params['serverpassword']);
    $answer = curl_exec($curl);
    $array = json_decode($answer,TRUE);
    if ($array['success'] != 'true'){
      return $array;
    }
    
    ## API request
    $data['_sid'] = $array['data']['sid'];
    $postdata = http_build_query($data);
    curl_setopt($curl, CURLOPT_URL, 'https://' . $params['serverhostname'] . ':' . $params['serverport'] . '/webapi/' . $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    $answer = curl_exec($curl);
    $request = json_decode($answer,TRUE);
        
    ## Logout
    #curl_setopt($curl, CURLOPT_URL, 'https://' . $params['serverhostname'] . ':' . $params['serverport'] . '/webapi/auth.cgi?api=SYNO.API.Auth&version=1&method=logout');
    #$answer = curl_exec($curl);
    #$array = json_decode($answer,TRUE);
    #if ($array['success'] != 'true'){
    #  return $array;
    #}
    curl_close($curl);
    return $request;
}

function puqPublicSynology_CreateAccount($params) {

  if ($params['server'] == 1) {
    $data = array(
      'api' => 'SYNO.Core.User',
      'method' => 'create',
      'version' => '1',
      'name' => $params['username'],
      'password' => $params['password'],
      'description' => $params['configoption2'],
      'email' => $params['clientsdetails']['email'],
      'cannot_chg_passwd' => 'false',
      'expired' => 'normal',
      'notify_by_email' => 'true',
      'send_password' => 'true',
    );
    #create user
    $create_user = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if(!$create_user){
      return 'API connect problem';
    }        
    if($create_user['success'] == 'true') {
      #set user group
      $data = array(
        'api' => 'SYNO.Core.Group.Member',
        'method' => 'add',
        'version' => '1',
        'group' => $params['configoption1'],
        'name'=> $params['username']
        );
      $set_user_group = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
      if(!$set_user_group){
        return 'API connect problem';
      }
      if($set_user_group['success'] != 'true') {
        return $set_user_group;
      }
      $result = 'success';   
    } else {
       return 'User creation problem, code: ' . $create_user['error']['code'];
    }
    return $result;
  }
}

function puqPublicSynology_TerminateAccount($params) {

    $data = array(
      'api' => 'SYNO.Core.User',
      'method' => 'set',
      'version' => '1',
      'name' => $params['username'],
      'expired'=> 'now',
    );
    $TerminateAccount = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if(!$TerminateAccount){
      return 'API connect problem';
    }
    if($TerminateAccount['success'] == 'true') {
      return 'success';
    }
    else{
      return 'Terminate account problem, code: ' . $TerminateAccount['error']['code'];
    }
}


function puqPublicSynology_SuspendAccount($params) {

    $data = array(
      'api' => 'SYNO.Core.User',
      'method' => 'set',
      'version' => '1',
      'name' => $params['username'],
      'expired'=> 'now',
    );
    $SuspendAccount = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if(!$SuspendAccount){
      return 'API connect problem';
    }
    if($SuspendAccount['success'] == 'true') {
      return 'success';
    }
    else{
      return 'Suspend account problem, code: ' . $SuspendAccount['error']['code'];
    }
}


function puqPublicSynology_UnsuspendAccount($params) {

    $data = array(
      'api' => 'SYNO.Core.User',
      'method' => 'set',
      'version' => '1',
      'name' => $params['username'],
      'expired'=> 'normal',
    );
    $UnsuspendAccount = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if(!$UnsuspendAccount){
      return 'API connect problem';
    }
    if($UnsuspendAccount['success'] == 'true') {
      return 'success';
    }
    else{
      return 'Unsuspend account problem, code: ' . $UnsuspendAccount['error']['code'];
    }
}


function puqPublicSynology_ChangePassword($params) {

    $data = array(
      'api' => 'SYNO.Core.User',
      'method' => 'set',
      'version' => '1',
      'name' => $params['username'],
      'cannot_chg_passwd'=> 'false',
      'password'=> $params['password'],
    );
    $ChangePassword = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if(!$ChangePassword){
      return 'API connect problem';
    }
    if($ChangePassword['success'] == 'true') {
      return 'success';
    }
    else{
      return 'Change password problem, code: ' . $ChangePassword['error']['code'];
    }
}


function puqPublicSynology_loadLangPUQ($params) {

  $lang = $params['model']['client']['language'];

  $langFile = dirname(__FILE__) . "/lang/" . $lang . ".php";
  if (!file_exists($langFile))
    $langFile = dirname(__FILE__) . "/lang/" . ucfirst($lang) . ".php";
  if (!file_exists($langFile))
    $langFile = dirname(__FILE__) . "/lang/english.php";
    
  require dirname(__FILE__) . '/lang/english.php';
  require $langFile;

  return $_LANG_PUQ;  
}

function puqPublicSynology_ClientArea($params) {

  $lang = puqPublicSynology_loadLangPUQ($params);
  $data = array(
      'api' => 'SYNO.Core.Quota',
      'method' => 'get',
      'version' => '1',
      'name' => $params['username'],
      'support_share_quota'=> 'true',
    );
  $curl = puqPublicSynology_apiCurl($params,$data,'entry.cgi');

  if(!$curl){
    return 'API connection problem.';
  }

  if($curl['success'] != 'true') {
    return 'API connection problem. Code: ' . $curl['error']['code'];
  }

  $home_group_limit = 0;
  $home_used = 0;
  foreach ($curl['data']['user_quota'] as $volume) {
    foreach ($volume['shares'] as $shares){
      if ($shares['name'] == 'homes'){
        $home_group_limit = $shares['group_limit'];
        $home_used = $shares['used'];
      }
    }
  }

  if($curl){  
    return array(
        'templatefile' => 'clientarea',
        'vars' => array(
            'lang' => $lang,
            'params'=> $params,
            'home_group_limit' => $home_group_limit,
            'home_used' => $home_used
        ),
    );
  }
}

function puqPublicSynology_UsageUpdate($params) {

  $table = 'tblhosting';
  $fields = 'username';
  $where = array('server'=>$params['serverid']);
  $result = select_query($table,$fields,$where);
  while ($dat = mysql_fetch_array($result)){
    $username = $dat['username'];    
    $data = array(
      'api' => 'SYNO.Core.Quota',
      'method' => 'get',
      'version' => '1',
      'name' => $username,
      'support_share_quota'=> 'true',
    );
    $curl = puqPublicSynology_apiCurl($params,$data,'entry.cgi');
    if($curl){
   
      $home_group_limit = 0;
      $home_used = 0;
      foreach ($curl['data']['user_quota'] as $volume) {
        foreach ($volume['shares'] as $shares){
            if ($shares['name'] == 'homes'){
              $home_group_limit = $shares['group_limit'];
              $home_used = $shares['used'];
            }
        }
      }
      update_query("tblhosting",array(
        'diskusage'=>$home_used,
        'disklimit'=>$home_group_limit,
        'bwusage'=>'0',
        'bwlimit'=>'0',
        'lastupdate'=>'now()',),array('server'=>$params['serverid'], 'username'=>$username));
    }
  }
}

function puqPublicSynology_AdminServicesTabFields($params) {

    $data = array(
      'api' => 'SYNO.Core.Quota',
      'method' => 'get',
      'version' => '1',
      'name' => $params['username'],
      'support_share_quota'=> 'true',
    );
    $curl = puqPublicSynology_apiCurl($params,$data,'entry.cgi');

    if(!$curl){
      $fieldsarray = array('API Connection Status' => '<div class="errorbox">API connection problem.</div>');
      return $fieldsarray;
    }

    if($curl['success'] != 'true') {
      $fieldsarray = array('API Connection Status' => '<div class="errorbox">API connection problem. Code: ' . $curl['error']['code']. '</div>');
      return $fieldsarray;
    }

    $home_group_limit = 0;
    $home_used = 0;
    foreach ($curl['data']['user_quota'] as $volume) {
       foreach ($volume['shares'] as $shares){
        if ($shares['name'] == 'homes'){
            $home_group_limit = $shares['group_limit'];
            $home_used = $shares['used'];
        }
       }
    }

    $fieldsarray = array(
     'API Connection Status' => '<div class="successbox">API Connection OK</div>',
     'Disk status' => '
                      <b>Total:</b> '.round($home_group_limit/ '1024') .' Gb <b>|</b> 
                      <b>Used:</b> '.round($home_used / '1024', 2) .' Gb , '.round('100' * $home_used / $home_group_limit).'%<b>|</b>',                        
    );
    return $fieldsarray;
}

?>
