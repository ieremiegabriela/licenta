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
        (SELECT COUNT(*)
        FROM
            (SELECT `friends`.*
            FROM `status`
            INNER JOIN `friends` ON (((`friends`.`sender` = `status`.`added_by`
                                        AND `friends`.`recipient` = ?)
                                        OR (`friends`.`recipient` = `status`.`added_by`
                                            AND `friends`.`sender` = ?))
                                    AND `friends`.`accepted` = 1
                                    AND `friends`.`enabled` = 1)
            WHERE `status`.`enabled` = 1
                AND `status`.`type` = 'safe'
                AND `status`.`added_on` > CURRENT_TIMESTAMP() - INTERVAL 1 DAY
            GROUP BY `friends`.`id`) AS `pseudo`) AS `count_status_safe_last_24`,

        (SELECT COUNT(*)
        FROM
            (SELECT `friends`.*
            FROM `status`
            INNER JOIN `friends` ON (((`friends`.`sender` = `status`.`added_by`
                                        AND `friends`.`recipient` = ?)
                                        OR (`friends`.`recipient` = `status`.`added_by`
                                            AND `friends`.`sender` = ?))
                                    AND `friends`.`accepted` = 1
                                    AND `friends`.`enabled` = 1)
            WHERE `status`.`enabled` = 1
                AND `status`.`type` = 'danger'
                AND `status`.`added_on` > CURRENT_TIMESTAMP() - INTERVAL 1 DAY
            GROUP BY `friends`.`id`) AS `pseudo`) AS `count_status_danger_last_24`,

        (SELECT COUNT(*)
        FROM
            (SELECT `friends`.*
            FROM `status`
            INNER JOIN `friends` ON (((`friends`.`sender` = `status`.`added_by`
                                        AND `friends`.`recipient` = ?)
                                        OR (`friends`.`recipient` = `status`.`added_by`
                                            AND `friends`.`sender` = ?))
                                    AND `friends`.`accepted` = 1
                                    AND `friends`.`enabled` = 1)
            WHERE `status`.`enabled` = 1
                AND `status`.`type` = 'safe'
                AND WEEK(`status`.`added_on`, 3) = WEEK(CURRENT_TIMESTAMP(), 3)
            GROUP BY `friends`.`id`) AS `pseudo`) AS `count_status_safe_this_week`,

        (SELECT UNIX_TIMESTAMP(`status`.`added_on`)
        FROM `status`
        WHERE `status`.`added_by` = ?
        ORDER BY `status`.`id` DESC
        LIMIT 1) AS `last_marked_timestamp`,

        (SELECT IF(`status`.`type` = 'safe', 'fa-solid fa-shield-halved', 'fa-solid fa-triangle-exclamation')
        FROM `status`
        WHERE `status`.`added_by` = ?
        ORDER BY `status`.`id` DESC
        LIMIT 1) AS `status_classes`";

// --------------------------------------------------

$params = array_fill(0, 8, $_SESSION['id']);
$type = str_repeat('i', sizeof($params));
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($type, ...$params);

if ($stmt->execute()):
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $row = array_combine(array_keys($row), array_map(
        function ($value, $key) {
            $excluded = [
                'last_marked_timestamp'
            ];

            switch (filter_var($value, FILTER_VALIDATE_INT)):
                default:
                case false:
                    return $value;
                case true && !in_array($key, $excluded, true):
                    return number_format($value, 0, (string)null, ".");
            endswitch;
        },
        array_values($row),
        array_keys($row)
    ));

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => [
            'countStatusSafeLast24' => $row['count_status_safe_last_24'],
            'countStatusDangerLast24' => $row['count_status_danger_last_24'],
            'countStatusSafeThisWeek' => $row['count_status_safe_this_week'],
            'lastMarkedTimestamp' => $row['last_marked_timestamp'],
            'statusClasses' => $row['status_classes']
        ]
    ];
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
    if (!defined("_navigation.php")) define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}/_navigation.php");
    ?>

    <!-- Control Section -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="container col-lg-10 px-0 py-2 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Stats & Controls -->
            <div class="card-list overflow-auto custom-border-radius p-1 h-100 d-flex justify-content-center">
                <div class="position-relative d-flex flex-column px-0 py-1 h-100">
                    <div class="d-flex flex-row justify-content-center">
                        <div class="row w-100">
                            <!-- Main Control Column -->
                            <div class="col-md-4 mb-2">
                                <!-- Main Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-safe btn-lg py-2 custom-border-radius">
                                        <i class="fa-solid fa-shield-halved"></i> I'm Safe
                                    </button>
                                    <button class="btn btn-unsafe btn-lg py-2 custom-border-radius">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Need Help
                                    </button>
                                    <!-- Stat Card 2 -->
                                    <div class="status-card card border-warning border-top-0 border-end-0 border-bottom-0 border-4">
                                        <div class="card-body text-center">
                                            <p class="mb-0"><i class="fa-regular fa-clock text-warning"></i>&nbsp;<strong>Last marked:</strong><br><span id="lastMarkTime" data-timestamp="<?php echo $output['data']['lastMarkedTimestamp'] ?>" data-status-classes="<?php echo $output['data']['statusClasses']; ?>"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- General Stats Column -->
                            <div class="col-md-8">
                                <div class="row g-2">
                                    <!-- Stat Card 1 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-success border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-success"><i class="fa-solid fa-shield-halved"></i>&nbsp;<?php echo $output['data']['countStatusSafeLast24']; ?></h2>
                                                <p class="mb-0"><strong>Marked as safe</strong> in last 24 hours</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stat Card 2 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-warning border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;<?php echo $output['data']['countStatusDangerLast24']; ?></h2>
                                                <p class="mb-0"><strong>Requests for help</strong> today</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stat Card 3 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-primary border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-primary"><i class="fa-solid fa-circle-info"></i>&nbsp;<?php echo $output['data']['countStatusSafeThisWeek']; ?></h2>
                                                <p class="mb-0"><strong>Total safe marks</strong> this week</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-grow-1 align-items-center justify-content-center">
                        <img class="img-fluid opacity-55" src="/assets/img/logo.png" alt="#" style="scale: 0.5;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>