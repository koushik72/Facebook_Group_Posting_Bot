<?php
// load autoload for composer packages
require('vendor/autoload.php');

// load the named consts file that contains all the sensitive info
require('includes/variables.php');

// load phpmailer settings
require('phpmailer.php');

// added because there may be the need to delete while testing
use Facebook\FacebookRequest;


$appsecret_proof = hash_hmac('sha256', token, app_secret);

// fb api
$fb = new Facebook\Facebook([
    'app_id' => app_id,
    'app_secret' => app_secret,
    'appsecret_proof' => $appsecret_proof,
    'default_graph_version' => 'v2.8'
]);

// $fb app
$fbApp = new \Facebook\FacebookApp(app_id, app_secret);

// make an array of the data that has to be posted
$dataToPost = [
    'landingPage' => [
        'link' => 'https://www.fiverr.com/s2/e6d69807e0',
        'message' => 'Get a beautiful landing page or squeeze page for your business at $10 (exclusively on Fiverr).
The page will catch your audience\'s attention.
Get a working contact form with google reCaptcha on the page for free'
    ],
    'PhpMySQLApp' => [
        'link' => 'https://www.fiverr.com/s2/d8b0e8bcbb',
        'message' => 'Get a Php and MySQL application done for your business.
I will build any Php or Mysql application that you need.'
    ]
];

// make an array of groups that need to be posted in
$groups = array('Entrepreneur' => '168892346490054', 'Internet Marketing' => '163901963701922', 'Affiliate Marketing and money making' => '184087555102491',
    'Entrepreneurs Hut' => '366463540226995', 'Bangalore Start Up Connect' => '1546978348891191', 'Start Up Talky' => '1628088717410164', 'Work from home' => '691810680861797',
    'Affiliate Marketing' => '127166864003113');

// my group used for testing
//$groups = array('My group'=> '1526456047379495');

// make array for storing the gig names and groups
$gigs = [];
$groupNames = [];

// post ids for capturing all the ids
$postIds = [];

// post each data into each group
foreach ($groups as $groupName => $groupId) {
    foreach ($dataToPost as $gigName => $gigLink) {
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->post('/' . $groupId . '/feed', $gigLink, token);

            // push gig names and group names into the vars
            array_push($gigs, $gigName);
            array_push($groupNames, $groupName);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $graphNode = $response->getGraphNode();
        array_push($postIds, $graphNode['id']);
        echo $gigName . ' posted into group ' . $groupName . ' with id: ' . $graphNode['id'] . '<br/>';
    }
}

// set the mail subject
$mail->Subject = "Facebook Bot Posting";

// if successfully posted, then gigs and groupnames array
// will have items, then send an email informing it has been sent
if ($gigs && $groupNames) {
    $gigs = array_unique($gigs, SORT_REGULAR);
    $groupNames = array_unique($groupNames, SORT_REGULAR);

    $msg = "Gigs: <br/>";
    foreach ($gigs as $gig) {
        $msg .= "<b>$gig</b>, ";
    }

    $msg .= "Posted into groups <br/>";
    foreach ($groupNames as $groupName) {
        $msg .= "<b>$groupName</b>, ";
    }

    $mail->Body = $msg . "<br/> Sent from post2.";

    if ($mail->send()) {
        echo "Mail sent <br/>";
    } else {
        echo "Mail not sent. Something wrong <br/>";
    }

}

/*Used only for testing*/
/*// delete all the posts
foreach ($postIds as $postId) {
    $request = new FacebookRequest(
        $fbApp,
        token,
        'DELETE',
        $postId
    );

    $response = $fb->getClient()->sendRequest($request);

    echo $postId . " deleted <br/>";
}*/