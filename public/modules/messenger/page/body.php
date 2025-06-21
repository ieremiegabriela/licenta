<?php

// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

if (!defined('parameters.php')) define('parameters.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/parameters.php");

if (!defined('db_connect.php')) define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - RETRIEVE PERSONAL CHATS ------------------

$sql =
    "SELECT
        `chats`.`id`,
        IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AS `correspondent`,

        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `correspondent_fullname`
    
    FROM `chats`
    LEFT JOIN `users` ON (`users`.`id` = IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AND `users`.`enabled` = 1)
    
    WHERE (`chats`.`sender` = ? OR `chats`.`recipient` = ?) AND `chats`.`enabled` = 1";

// --------------------------------------------------

$params = array_fill(0, 4, $_SESSION['id']);
$types = str_repeat("i", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();
    $temp = [];

    while ($row = $result->fetch_assoc()):

        $temp[] = [
            'id' => $row['id'],
            'correspondentId' => $row['correspondent'],
            'correspondentFullname' => $row['correspondent_fullname'],
        ];
    endwhile;

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $temp
    ];

    unset($temp);
else:

    $output = [
        'success' => 0,
        'message' => 'Ooops! Something went wrong...',
        'data' => null
    ];
endif;

mysqli_stmt_close($stmt);

// END - RETRIEVE PERSONAL CHATS --------------------


// BEGIN - RETRIEVE LAST MESSAGE & UNREAD COUNT -----

$sql = [];

foreach ($output['data'] as $element):

    $sql[] =
        "SELECT * 
        
        FROM (
            SELECT
                `chat_{$element['id']}`.`message` AS `last_message`,
                `chat_{$element['id']}`.`added_on`,
                UNIX_TIMESTAMP(`chat_{$element['id']}`.`added_on`) AS `added_on_unix`,
                (SELECT COUNT(*) AS `count` FROM (SELECT * FROM `chat_{$element['id']}`) AS `alias_{$element['id']}`
                WHERE `alias_{$element['id']}`.`seen` = 0) AS `unread_count`
                

            FROM `chat_{$element['id']}`

            WHERE `chat_{$element['id']}`.`enabled` = 1
            
            ORDER BY `chat_{$element['id']}`.`id` DESC
            LIMIT 1) AS `chat_{$element['id']}`
        UNION ALL

        SELECT NULL, NULL, NULL, NULL
        WHERE NOT EXISTS (
            SELECT 1 FROM `chat_{$element['id']}` WHERE `enabled` = 1
        )";
endforeach;

$sql = implode(" UNION ", $sql);

// --------------------------------------------------

if (strlen($sql)) $stmt = $mysqli->prepare($sql);

if (strlen($sql) && $stmt->execute()):

    $result = $stmt->get_result();

    $count = 0;
    while ($row = $result->fetch_assoc()):

        switch (!$row['last_message']):
            default:
            case true:
                unset($output['data'][$count]);
                break;

            case false:
                $output['data'][$count]['lastMessage'] = $row['last_message'];
                $output['data'][$count]['addedOn'] = $row['added_on'];
                $output['data'][$count]['addedOnUnix'] = $row['added_on_unix'];
                $output['data'][$count]['unreadCount'] = $row['unread_count'];
                break;
        endswitch;

        $count++;
    endwhile;

    array_multisort(array_column($output['data'], "addedOnUnix"), SORT_DESC, $output['data']);

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $output['data']
    ];
else:

    $output = [
        'success' => 0,
        'message' => 'Ooops! Something went wrong...',
        'data' => null
    ];
endif;

if (strlen($sql)):
    mysqli_stmt_close($stmt);
else:
    $output['data'] = [];
endif;

// END - RETRIEVE LAST MESSAGE & UNREAD COUNT -------
?>

<body class="h-100 pt-5">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="assets/img/loading.gif" alt="#">
    </div>

    <!-- Navigation -->
    <?php
    if (!defined("_navigation.php")) define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}_navigation.php");
    ?>

    <!-- Page Content -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="container col-lg-10 p-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Title -->
            <div class="px-2 pt-3 d-flex flex-row justify-content-between">
                <h1 class="fw-bold m-0 pb-1 text-dark">Chats</h1>
                <h1 class="fw-bold m-0 pb-1 text-dark">
                    <a href="#"><i class="fa-solid fa-comment-medical text-info"></i></a>
                </h1>
            </div>

            <!-- Chats -->
            <div class="card-list overflow-auto custom-border-radius p-1" style="height: calc(100% - 4.9rem);">
                <?php
                // BEGIN - BUILD THE HTML CHAT CARDS ----------------

                foreach ($output['data'] as $element):
                ?>
                    <div class="card pt-2 px-2 border-0 bg-light" data-id="<?php echo $element['id']; ?>">
                        <div class="position-absolute top-0 end-0 z-1">
                            <div class="badge badge-circle bg-warning ms-5 h-100 custom-border-radius <?php echo ((int)$element['unreadCount'] !== 0 ? 'visible' : 'invisible'); ?>">
                                <span><?php echo $element['unreadCount']; ?></span>
                            </div>
                        </div>

                        <a href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/chat.php?id={$element['id']}"; ?>" class="card border-2 text-reset mt-1 mb-1 z-0 shadow-sm custom-border-radius">
                            <div class="card-body py-1">
                                <div class="row">
                                    <div class="d-flex justify-content-between px-0">
                                        <div style="width: 70px;"><img src="assets/img/message.png" alt="#" class="img-fluid"></div>
                                        <div class="col px-0">
                                            <div class="d-flex justify-content-between my-1 ms-1">
                                                <h5 class="me-auto mb-0"><?php echo $element['correspondentFullname']; ?></h5>
                                                <span class="chat-timestamp text-muted extra-small ms-2 custom-border-radius h-100" data-unix-epoch="<?php echo $element['addedOnUnix']; ?>"></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center custom-border-radius p-2 ms-1 border <?php echo ((int)$element['unreadCount'] !== 0 ? 'bg-warning-subtle' : ''); ?>">
                                                <span class="line-clamp me-auto lh-sm"><?php echo $element['lastMessage']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
                endforeach;

                // --------------------------------------------------

                if (!sizeof($output['data'])):
                ?>
                    <div class="message-container d-flex flex-column justify-content-center align-items-center mt-5 mb-1 ms-auto me-auto">
                        <div class="empty-chat message bg-info-subtle p-2 custom-border-radius border fs-5">No chats here yet. Be the first to start a conversation!</div>
                    </div>
                <?php
                endif;

                // END - BUILD THE HTML CHAT CARDS ------------------
                ?>
            </div>
            <!-- Chats -->
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>