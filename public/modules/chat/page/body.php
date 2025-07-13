<?php

// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

use GrahamCampbell\ResultType\Success;

if (!defined('load_env.php')) define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

if (!defined('db_connect.php')) define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - ADDITIONAL SECURITY CHECK ----------------

$sql =
    "SELECT * FROM `chats`
    
    WHERE `chats`.`id` = ?
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)
    
    LIMIT 1";

// --------------------------------------------------

$params = [
    $input['id'],
    $_SESSION['id']
];
$types = str_repeat("i", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();
    if (!$result->num_rows) die();

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];
else: die();
endif;

mysqli_stmt_close($stmt);

// END - ADDITIONAL SECURITY CHECK ------------------


// BEGIN - RETRIEVE MESSAGE LIST FROM DB ------------

$sql =
    "SELECT
        `chat_{$input['id']}`.`id`,
        `chat_{$input['id']}`.`sender`,
        `chat_{$input['id']}`.`recipient`,
        `chat_{$input['id']}`.`message`,
        `chat_{$input['id']}`.`seen`,
        `chat_{$input['id']}`.`added_on`,
        UNIX_TIMESTAMP(`chat_{$input['id']}`.`added_on`) AS `timestamp`
    
    FROM `chat_{$input['id']}`";

// --------------------------------------------------

$stmt = $mysqli->prepare($sql);

if ($stmt->execute()):

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];

    $result = $stmt->get_result();
    $output['data']['rowCount'] = $result->num_rows;
    $output['data']['messages'] = [];

    while ($row = $result->fetch_assoc()):

        $output['data']['messages'][] = [
            'id' => $row['id'],
            'sender' => $row['sender'],
            'recipient' => $row['recipient'],
            'message' => $row['message'],
            'seen' => $row['seen'],
            'status' => (int)$row['seen'] === 1 ? "Seen" : "Delivered",
            'addedOn' => $row['added_on'],
            'timestamp' => $row['timestamp']
        ];
    endwhile;

    foreach ($output['data']['messages'] as $element):
        switch ($element['sender'] === $_SESSION['id']):
            case true:
                $output['data']['lastSenderMessageId'] = $element['id'];
                break;
            case false:
                $output['data']['lastRecipientMessageId'] = $element['id'];
                break;
        endswitch;
    endforeach;

    if ($output['data']['rowCount']):
        array_multisort(array_column($output['data']['messages'], 'id'), SORT_DESC, $output['data']['messages']);
    endif;
else: die();
endif;

mysqli_stmt_close($stmt);

// END - RETRIEVE LAST MESSAGE & UNREAD COUNT -------


// BEGIN - RETRIEVE CORRESPONDENT NAME --------------

$sql =
    "SELECT
        IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AS `correspondent`,
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `correspondent_fullname`
    
    FROM `chats`
    LEFT JOIN `users` ON `users`.`id` = IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`)

    WHERE `chats`.`id` = ?";

// --------------------------------------------------

$params = array_fill(0, 2, $_SESSION['id']);
$params[] = $input['id'];
$types = str_repeat("i", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $output['data']
    ];

    $result = $stmt->get_result();
    if (!$result->num_rows) die();

    $row = $result->fetch_assoc();
    $output['data']['correspondent'] = $row['correspondent'];
    $output['data']['correspondentFullname'] = $row['correspondent_fullname'];
else: die();
endif;

mysqli_stmt_close($stmt);

// BEGIN - RETRIEVE CORRESPONDENT NAME --------------


// BEGIN - AUTHORIZE ACTION -------------------------

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "{$_SESSION['DOCKER_ORIGIN']}/modules/chat/handlers/authorize_action.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'userId' => $output['data']['correspondent'],
    'action' => "send-message"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID={$_COOKIE['PHPSESSID']}; path=/");
session_write_close();

// Capture the response
$response = curl_exec($ch);
curl_close($ch);

$jsonObj = (array)json_decode($response, true);

// END - AUTHORIZE ACTION ---------------------------


// BEGIN - MARK SEEN STATUS -------------------------

$sql =
    "UPDATE `chat_{$input['id']}`
    
    SET `chat_{$input['id']}`.`seen` = 1
    WHERE `chat_{$input['id']}`.`recipient` = ?";

// --------------------------------------------------

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);

if ($stmt->execute()):

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $output['data']
    ];
else: die();
endif;

mysqli_stmt_close($stmt);

// END - MARK SEEN STATUS ---------------------------
?>

<body class="h-100 pt-5">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="/assets/img/loading.gif" alt="#" />
    </div>

    <!-- Navigation -->
    <?php
    if (!defined("_navigation.php")) define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}/_navigation.php");
    ?>

    <!-- Page Content -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="position-relative container d-flex flex-column col-lg-10 p-0 py-2 bg-light border border-2 border-primary-subtle shadow custom-border-radius h-100">
            <!-- Title -->
            <div class="pt-2 px-2 d-flex flex-row w-100">
                <h1 class="fw-bold m-0 pb-1 text-dark border-bottom border-2 w-100">
                    <a id="anchorBack" href="/messenger.php">
                        <i class="fa-solid fa-circle-left text-info"></i>
                    </a>
                    <span class=""><?php echo $output['data']['correspondentFullname']; ?></span>
                </h1>
            </div>

            <!-- Messages -->
            <div id="messageContainer" class="d-flex flex-column-reverse overflow-auto card-list p-2 custom-border-radius flex-grow-1">
                <div class="flex-grow-1"></div>
                <?php
                switch ($output['data']['rowCount']):
                    case true:
                        if (!$jsonObj['success']):
                ?>
                            <div class="message-container d-flex flex-column justify-content-center align-items-center mb-1 ms-auto me-auto">
                                <div class="conv-info message bg-info-subtle p-2 custom-border-radius border">You can no longer correspond with this contact at this time</div>
                            </div>
                            <?php
                        endif;

                        foreach ($output['data']['messages'] as $index => $element):
                            switch ($element['sender'] === $_SESSION['id']):
                                case true:
                            ?>
                                    <div class="message-container d-flex flex-column justify-content-end align-items-end mb-1 ms-auto">
                                        <div class="message bg-info-subtle p-2 custom-border-radius border"><?php echo $element['message']; ?></div>
                                        <span class="message-status text-secondary small px-1 <?php echo ($output['data']['lastSenderMessageId'] === $element['id'] ? "last-message" : "d-none"); ?>" data-unix-epoch="<?php echo $element['timestamp']; ?>" data-status="<?php echo $element['status']; ?>"></span>
                                    </div>
                                <?php
                                    break;
                                case false:
                                ?>
                                    <div class="message-container d-flex flex-column justify-content-start align-items-start mb-1 me-auto">
                                        <div class="message bg-warning-subtle p-2 custom-border-radius border"><?php echo $element['message']; ?></div>
                                        <span class="message-status text-secondary small px-1 <?php echo ($output['data']['lastRecipientMessageId'] === $element['id'] ? "last-message" : "d-none") ?>" data-unix-epoch="<?php echo $element['timestamp']; ?>"></span>
                                    </div>
                        <?php
                                    break;
                            endswitch;
                        endforeach;
                        break;

                    case false:
                        ?>
                        <div class="message-container d-flex flex-column justify-content-center align-items-center mb-1 ms-auto me-auto">
                            <div class="conv-info message bg-info-subtle p-2 custom-border-radius border">No messages yet. Be the first to start the conversation!</div>
                        </div>
                <?php
                        break;
                endswitch;
                ?>
            </div>

            <?php
            if ($jsonObj['success']):
            ?>
                <form name="sendMessageForm" id="sendMessageForm" class="d-flex p-2 border-0 w-100" action="#" autocomplete="off">
                    <input id="messageInput" name="messageInput" type="text" class="form-control me-1 custom-border-radius" placeholder="Type message..." autocomplete="off">
                    <button class="btn btn-primary ms-1 custom-border-radius bg-secondary-subtle border-0 text-shadow" type="submit" style="cursor: not-allowed;">Send</button>
                </form>
            <?php
            endif;
            ?>
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>