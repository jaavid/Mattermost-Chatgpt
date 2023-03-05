<?php

// Load required libraries
include 'vendor/autoload.php';

// Define Mattermost variables
$mattermostBaseUrl = 'https://your-mattermost-url.com';
$mattermostToken = 'YOUR_ACCESS_TOKEN';

// Define OpenAI variables
$openaiSecretKey = 'OPENAI_ACCESS_TOKEN';


// Import required classes
use GuzzleHttp\Client;
use OpenAI\OpenAI;

// Extract relevant information from POST request
$channelId  = $_POST['channel_id'];
$userId     = $_POST['user_id'];
$message    = $_POST['text'];

// Create a new GuzzleHTTP client to communicate with the Mattermost API
$client = new Client([
    'base_uri' => $mattermostBaseUrl .'/api/v4/',
    'headers' => [
        'Authorization' => 'Bearer '.$mattermostToken,
        'Content-Type' => 'application/json'
    ]
]);

// Create a new OpenAI client to communicate with the OpenAI API
$oaclient = OpenAI::client($openaiSecretKey);

// Use OpenAI client to generate a response to the user's message
$response = $oaclient->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => $message],
    ],
]);

// Extract the response from the OpenAI API
$gptanswer = "";
foreach ($response->choices as $result) {
    $gptanswer .= $result->message->content;
}

// Send POST request to Mattermost API with response message and attachment
$res = $client->post('posts', [
    'json' => [
        'channel_id' => $channelId,
        'message' => "### " . $_POST['user_name'] . " : \n ** " . $message." **",
        'props' => [
            'attachments' => [
                [
                    'text'          => $gptanswer,
                    'color'         => '#36a64f',
                    'footer'        => 'ChatGPT',
                    'footer_icon'   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/ChatGPT_logo.svg/240px-ChatGPT_logo.svg.png',
                ]
            ]
        ]
    ]
]);