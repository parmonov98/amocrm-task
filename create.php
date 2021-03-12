<?php
include_once __DIR__ . '/vendor/autoload.php';

define('TOKEN_FILE', 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

getContacts();

function getContacts()
{

  $method = 'contacts';

  $response = sendMessage([], $method);

  // print_r($response);

  $count = count($response['_embedded']['contacts']);

  $contacts = $response['_embedded']['contacts'];
  $tasks = [];
  for ($i = 0; $i < $count; $i++) {
    $contact = $contacts[$i];

    // print_r($user);

    $tasks[] = [
      'text' => 'Тестовая задача на вакансию php-программист',
      "complete_till" => time() + 3600,
      "entity_id" => $contact['id'],
      "entity_type" => 'contacts'
    ];
  }

  $res = sendMessage($tasks,  $method = 'tasks');
  print_r($res);
}


/**
 * @return \League\OAuth2\Client\Token\AccessToken
 */
function getToken()
{
  return json_decode(file_get_contents(TOKEN_FILE), true);
}


function sendMessage($content, $method)
{

  $tokenInfo = getToken();


  $subdomain = $tokenInfo['baseDomain']; //Subdomain of the account in question
  $link = 'https://' . $subdomain . '/api/v4/' . $method; //Creation of URL for request
  /** Getting access_token from your storage */
  $access_token = $tokenInfo['accessToken'];
  /** Creating headers */
  $headers = [
    'Authorization: Bearer ' . $access_token
  ];
  /**
   * We need to initiate a request to the server.
   * Let’s use library with cURL
   * You can also use cross platform cURL, if you don’t code on PHP.
   */
  $curl = curl_init(); //Saving descriptor of cURL
  /** Installing required options for session cURL  */
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_HEADER, false);
  if (!empty($content)) {
    // echo 11111111;
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($content));
  }
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Initiating request to API and saving reply to variable
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  /** Now we can process replies from the server. It’s an example, you can process this data however you want to. */
  $code = (int)$code;
  $errors = [
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  ];

  return json_decode($out, 1);
}
