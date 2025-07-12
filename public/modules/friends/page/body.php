<?php
// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

if (!defined('load_env.php')) define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

if (!defined('db_connect.php')) define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - IDENTIFY CORRESPONDENT -------------------

$sql =
    "SELECT 
        `pseudo`.`status`,
        `pseudo`.`correspondent`,
        `pseudo`.`status_classes`,
        `pseudo`.`timestamp`,
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `name`,
        CASE
            WHEN `pseudo`.`status` = 'Accepted' THEN 'Remove'
            WHEN `pseudo`.`sender` = ? AND `pseudo`.`status` = 'Pending' THEN 'Revoke'
            WHEN `pseudo`.`recipient` = ? AND `pseudo`.`status` = 'Pending' THEN 'Accept|Revoke'
        END AS `actions`,
        CASE
            WHEN `pseudo`.`status` = 'Accepted' THEN 'remove fa-solid fa-user-minus text-dark'
            WHEN `pseudo`.`sender` = ? AND `pseudo`.`status` = 'Pending' THEN 'revoke fa-solid fa-xmark text-dark'
            WHEN `pseudo`.`recipient` = ? AND `pseudo`.`status` = 'Pending' THEN 'accept fa-solid fa-check text-dark|revoke fa-solid fa-xmark text-dark'
        END AS `action_classes`,
        CASE
            WHEN `pseudo`.`status` = 'Accepted' THEN 'remove-friend'
            WHEN `pseudo`.`sender` = ? AND `pseudo`.`status` = 'Pending' THEN 'revoke-request'
            WHEN `pseudo`.`recipient` = ? AND `pseudo`.`status` = 'Pending' THEN 'accept-request|revoke-request'
        END AS `selection_classes`
        
    FROM (
        SELECT
            `friends`.`sender`,
            `friends`.`recipient`,
            UNIX_TIMESTAMP(`friends`.`added_on`) AS `timestamp`,
            IF(`friends`.`accepted` = 1, 'Accepted', 'Pending') AS `status`,
            IF(`friends`.`accepted` = 1, 'fa-regular fa-circle-check', 'fa-regular fa-clock') AS `status_classes`,
            IF(`friends`.`sender` = ?, `friends`.`recipient`, `friends`.`sender`) AS `correspondent`

        FROM `friends`
        WHERE `friends`.`enabled` = 1
        AND ? IN (`friends`.`sender`, `friends`.`recipient`)) AS `pseudo`
    LEFT JOIN `users` ON `users`.`id` = `pseudo`.`correspondent`";

// --------------------------------------------------

$params = array_fill(0, 8, $_SESSION['id']);
$type = str_repeat('i', sizeof($params));
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($type, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();

    $temp = [];
    $count = 0;
    while ($row = $result->fetch_assoc()):

        $row['actions'] = explode("|", $row['actions']);
        $row['action_classes'] = explode("|", $row['action_classes']);
        $row['selection_classes'] = explode("|", $row['selection_classes']);

        $wClass = $row['status'] === "Accepted" || sizeof($row['action_classes']) === 2 ? "w-25" : "w-50";

        $temp[] = [
            'picture' => "<img class=\"me-1\" src=\"assets/img/user.png\" alt=\"#\" height=\"40\">",
            'name' => "<span>{$row['name']}</span>",
            'status' => "<span class=\"me-1\">{$row['status']}</span><i class=\"{$row['status_classes']}\"></i>",
            'actions' => "<button class=\"$wClass m-1 mybtn bg-white border border-2 border-primary-subtle {$row['selection_classes'][0]}\" data-bs-toggle=\"tooltip\" data-bs-title=\"{$row['actions'][0]}\" data-id=\"{$row['correspondent']}\"><i class=\"{$row['action_classes'][0]}\"></i></button>",
            'timestamp' => (int)$row['timestamp']
        ];

        $temp[$count]['actions'] .= $row['status'] === "Accepted" ? "<button class=\"$wClass m-1 mybtn bg-white border border-2 border-primary-subtle send-message\" data-bs-toggle=\"tooltip\" data-bs-title=\"Send Message\" data-id=\"{$row['correspondent']}\"><i class=\"fa-solid fa-comment text-dark\"></i></button>" : (string)null;
        $temp[$count]['actions'] .= sizeof($row['action_classes']) === 2 ? "<button class=\"$wClass m-1 mybtn bg-white border border-2 border-primary-subtle {$row['selection_classes'][1]}\" data-bs-toggle=\"tooltip\" data-bs-title=\"{$row['actions'][1]}\" data-id=\"{$row['correspondent']}\"><i class=\"{$row['action_classes'][1]}\"></i></button>" : (string)null;

        $count++;
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

// END - IDENTIFY CORRESPONDENT ---------------------
?>

<body class="h-100 pt-5">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="/assets/img/loading.gif" alt="#">
    </div>

    <!-- Modal -->
    <div id="addFriendsModal" class="modal modal-lg" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title">Add Friends</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pb-0 mb-2">
                    <!-- Alerts -->
                    <div class="alert alert-danger alert-dismissible d-none friend-request-danger" role="alert">
                        <strong>Ooops!</strong>&nbsp;Something went wrong...
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="alert alert-success alert-dismissible d-none friend-request-success" role="alert">
                        <strong>Success!</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <!-- Alerts -->

                    <form class="mb-2" action="#" name="userSearch" id="userSearch">
                        <input name="searchBox" id="searchBox" class="w-100 form-control" type="text" placeholder="Search for users...">
                    </form>

                    <!-- Search Results -->
                    <div class="search-results d-flex flex-column overflow-auto" style="max-height: calc(100vh - 20rem);"></div>
                    <!-- Search Results -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button name="allResultsBtn" id="allResultsBtn" type="button" class="btn btn-primary disabled">Load All Results</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <?php
    define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}/_navigation.php");
    ?>

    <!-- Page Content -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="container col-lg-10 p-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Title -->
            <div class="px-2 pt-3 d-flex flex-row justify-content-between">
                <h1 class="fw-bold m-0 pb-1 text-dark">Friends</h1>
                <h1 class="fw-bold m-0 pb-1 text-dark">
                    <button class="clear-btn" data-bs-toggle="modal" data-bs-target="#addFriendsModal"><i class="fa-solid fa-people-arrows text-info"></i></button>
                </h1>
            </div>

            <!-- Friends -->
            <div class="card-list overflow-auto custom-border-radius p-1" style="height: calc(100% - 3.7rem);">
                <table id="friendsTable" class="display">
                    <thead>
                        <tr>
                            <th colspan="2">Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($output['data'] as $index => $element):
                        ?>
                            <tr>
                                <td><?php echo $element['picture']; ?></td>
                                <td><?php echo $element['name']; ?></td>
                                <td><?php echo $element['status']; ?></td>
                                <td><?php echo $element['actions']; ?></td>
                                <td><?php echo $element['timestamp']; ?></td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Friends -->
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>