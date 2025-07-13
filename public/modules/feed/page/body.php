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
        `pseudo`.*,
        UNIX_TIMESTAMP(`pseudo`.`added_on`) AS `last_marked_timestamp`,
        IF(`pseudo`.`type` = 'safe', 'fa-solid fa-shield-halved', 'fa-solid fa-triangle-exclamation') AS `status_classes`,
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `name`

    FROM
        (SELECT *
        FROM `status`
        WHERE `status`.`enabled` = 1
        GROUP BY `status`.`added_by`
        ORDER BY `status`.`id` DESC) AS `pseudo`
    INNER JOIN `friends` ON (((`friends`.`sender` = `pseudo`.`added_by`
                            AND `friends`.`recipient` = ?)
                            OR (`friends`.`recipient` = `pseudo`.`added_by`
                                AND `friends`.`sender` = ?))
                            AND `friends`.`accepted` = 1
                            AND `friends`.`enabled` = 1)
    INNER JOIN `users` ON (`users`.`id` = `pseudo`.`added_by` AND
                            `users`.`enabled` = 1)
    ORDER BY FIELD(`pseudo`.`type`, 'danger', 'safe'),
            `pseudo`.`id` DESC";

// --------------------------------------------------

$params = array_fill(0, 2, $_SESSION['id']);
$type = str_repeat('i', sizeof($params));
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($type, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();

    $temp = [];
    $count = 0;
    while ($row = $result->fetch_assoc()):
        $temp[] = [
            'picture' => "<img class=\"me-1\" src=\"assets/img/user.png\" alt=\"#\" height=\"40\">",
            'name' => "<span>{$row['name']}</span>",
            'status' => "<span class=\"last-mark-time\" data-timestamp=\"{$row['last_marked_timestamp']}\" data-status-classes=\"{$row['status_classes']}\"></span>",
            'actions' => "<button class=\"w-50 m-1 mybtn bg-white border border-2 border-primary-subtle send-message\" data-bs-toggle=\"tooltip\" data-bs-title=\"Send Message\" data-id=\"{$row['added_by']}\"><i class=\"fa-solid fa-comment text-dark\"></i></button>",
            'timestamp' => (int)$row['last_marked_timestamp']
        ];

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
                <h1 class="fw-bold m-0 pb-1 text-dark">Feed</h1>
            </div>

            <!-- Feed -->
            <div class="card-list overflow-auto custom-border-radius p-1" style="height: calc(100% - 3.9rem);">
                <table id="feedTable" class="display">
                    <thead>
                        <tr>
                            <th colspan="2">Name</th>
                            <th>Last Marked</th>
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
            <!-- Feed -->
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>