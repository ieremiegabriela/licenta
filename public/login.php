<?php

// BEGIN - SESSION CHECK ----------------------------

session_start();

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']):

    die(header("Location: {$_SESSION['LOCATION_ORIGIN']}/index.php"));
endif;

// END - SESSION CHECK ------------------------------


// BEGIN - INITIAL CONFIG ---------------------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/config.php");

// END - INITIAL CONFIG -----------------------------

?>

<!DOCTYPE html>
<html class="h-100" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>

    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">

    <!-- -------------------------------------------------- -->

    <?php
    define("_libs.php", true);
    require_once("{$_SERVER['DOCUMENT_ROOT']}_libs.php");
    ?>

    <!-- -------------------------------------------------- -->

    <link rel="stylesheet" href="/helpers/css/custom.css">

    <script type="text/javascript" src="/helpers/js/helper_functions.js"></script>
    <script type="text/javascript" src="/modules/login/login.js"></script>
</head>

<body class="h-100">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="/assets/img/loading.gif" alt="#">
    </div>

    <div class="container h-100 pt-5">
        <div class="row justify-content-md-center">
            <div class="col-lg-6">

                <?php
                if (isset($_SESSION['mismatchedCredentials']) && $_SESSION['mismatchedCredentials']):
                    unset($_SESSION['mismatchedCredentials']);
                ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <strong>Holy guacamole!</strong>&nbsp;Incorrect credentials.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                endif;
                ?>


                <div id="loginSection">
                    <div class="myform form custom-border-radius border border-2 border-info-subtle">
                        <div class="logo py-2">
                            <div class="col-md-12 text-center d-flex flex-row justify-content-between align-items-center">
                                <h1 class="">Login</h1>
                                <img class="logo img-fluid" src="/assets/img/logo.png" alt="#">
                            </div>
                        </div>

                        <form class="pt-2" action="modules/login/handlers/login_form_action.php" method="post" name="login">
                            <div class="form-group mb-3">
                                <label for="emailLogin">Email address</label>
                                <input type="email" name="emailLogin" class="form-control" id="emailLogin" aria-describedby="emailHelp" placeholder="Enter email">
                            </div>
                            <div class="form-group mb-3">
                                <label for="passwordLogin">Password</label>
                                <input type="password" name="passwordLogin" id="passwordLogin" class="form-control" aria-describedby="emailHelp" placeholder="Enter Password">
                            </div>
                            <div class="form-group mb-3">
                                <p class="text-center">By signing up you accept our <a href="#">Terms Of Use</a></p>
                            </div>
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-block mybtn btn-primary tx-tfm w-100">Login</button>
                            </div>
                            <div class="col-md-12">
                                <div class="login-or">
                                    <hr class="hr-or">
                                    <span class="span-or">or</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <p class="text-center">Don't have account? <a href="#" id="signup">Sign up here</a></p>
                            </div>
                        </form>

                    </div>
                </div>

                <div id="registerSection">
                    <div class="myform form custom-border-radius border border-2 border-info-subtle">
                        <div class="logo py-2">
                            <div class="col-md-12 text-center d-flex flex-row justify-content-between align-items-center">
                                <h1 class="">Signup</h1>
                                <img class="logo img-fluid" src="/assets/img/logo.png" alt="#">
                            </div>
                        </div>

                        <form class="pt-2" action="modules/login/handlers/register_form_action.php" method="post" name="registration">
                            <div class="form-group mb-3">
                                <label for="firstnameRegister">First Name</label>
                                <input type="text" name="firstnameRegister" class="form-control" id="firstnameRegister" aria-describedby="emailHelp" placeholder="Enter Firstname">
                            </div>
                            <div class="form-group mb-3">
                                <label for="lastnameRegister">Last Name</label>
                                <input type="text" name="lastnameRegister" class="form-control" id="lastnameRegister" aria-describedby="emailHelp" placeholder="Enter Lastname">
                            </div>
                            <div class="form-group mb-3">
                                <label for="emailRegister">Email address</label>
                                <input type="email" name="emailRegister" class="form-control" id="emailRegister" aria-describedby="emailHelp" placeholder="Enter email">
                            </div>
                            <div class="form-group mb-3">
                                <label for="passwordRegister">Password</label>
                                <input type="password" name="passwordRegister" id="passwordRegister" class="form-control" aria-describedby="emailHelp" placeholder="Enter Password">
                            </div>
                            <div class="form-group mb-3">
                                <label for="confirmPasswordRegister">Confirm Password</label>
                                <input type="password" name="confirmPasswordRegister" id="confirmPasswordRegister" class="form-control" aria-describedby="emailHelp" placeholder="Enter Password">
                            </div>

                            <div class="col-md-12 text-center mb-3">
                                <button type="submit" class="btn btn-block mybtn btn-primary tx-tfm w-100">Create Account</button>
                            </div>
                            <div class="col-md-12">
                                <div class="login-or">
                                    <hr class="hr-or">
                                    <span class="span-or">or</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <p class="text-center"><a href="#" id="signin">Already have an account?</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>