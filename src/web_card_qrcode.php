<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/lib/WebCardQrcode/WebCardQrcode.php';

$web_card_qrcode_instance = new WebCardQrcode();

$edit = false;
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["edit"]) && $_GET["edit"] == "true" && isset($_GET["id"])) {
    $edit = true;
    $web_card_qrcode = $web_card_qrcode_instance->getQrcode($_GET["id"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["del_id"])) {
    $web_card_qrcode_instance->deleteQrcode($_POST["del_id"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit"])) {
    if (
        isset($_POST["identifier"]) &&
        isset($_POST["filename"]) &&
        isset($_POST["state"]) &&
        isset($_POST["id_owner"]) &&
        isset($_POST["id"]) &&
        isset($_POST['full_name']) && !empty($_POST['full_name']) &&
        isset($_POST['nickname']) &&
        isset($_POST['email']) &&
        isset($_POST['website']) &&
        isset($_POST['phone_number']) && !empty($_POST['phone_number']) &&
        isset($_POST['work_phone_number']) &&
        isset($_POST['role']) &&
        isset($_POST['note']) &&
        isset($_POST['photo']) && 
        isset($_POST['address']) &&
        isset($_POST['city']) &&
        isset($_POST['post_code']) &&
        isset($_POST['state']) &&
        isset($_POST['country'])
    ){
        $web_card_qrcode_instance->editQrcode($_POST);
    }else{
        $_SESSION['failure'] = "El nombre completo y el teléfono son requeridos.";
        header('Location: web_card_qrcode.php?edit=true&id='.$_GET["id"]);
    	exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["edit"])) {
    if (
        isset($_POST["foreground"]) &&
        isset($_POST["background"]) &&
        isset($_POST["filename"]) &&
        isset($_POST["format"]) &&
        isset($_POST["id_owner"]) &&
        isset($_POST['full_name']) && !empty($_POST['full_name']) &&
        isset($_POST['nickname']) &&
        isset($_POST['email']) &&
        isset($_POST['website']) &&
        isset($_POST['phone_number']) && !empty($_POST['phone_number']) &&
        isset($_POST['work_phone_number']) &&
        isset($_POST['role']) &&
        isset($_POST['note']) &&
        isset($_POST['photo']) &&
        isset($_POST['address']) &&
        isset($_POST['city']) &&
        isset($_POST['post_code']) &&
        isset($_POST['state']) &&
        isset($_POST['country'])
    ){
        $web_card_qrcode_instance->addQrcode($_POST);
    }else{
        $_SESSION['failure'] = "El nombre completo y el teléfono son requeridos.";
        header('Location: web_card_qrcode.php?id='.$_GET["id"]);
    	exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<title>Qrcode Generator</title>

<head>
    <?php include './includes/head.php'; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php include './includes/navbar.php'; ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php include './includes/sidebar.php'; ?>
        <!-- /.Main Sidebar Container -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">

                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark"><?php echo ($edit) ? "Edit" : "Add"; ?> Qr code</h1>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Flash messages -->
            <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
            <!-- /.Flash messages -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Enter the requested data</h3>
                        </div>
                        <form class="form" action="" method="post" id="web_card_form" enctype="multipart/form-data">
                            <div class="card-body">
                                <?php
                                if ($edit) {
                                    include BASE_PATH . '/forms/form_web_card_edit.php';
                                } else {
                                    include BASE_PATH . '/forms/form_web_card_add.php';
                                }
                                ?>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div><!--/. container-fluid -->
            </section><!-- /.content -->
        </div><!-- /.content-wrapper -->

        <!-- Footer and scripts -->
        <?php include './includes/footer.php'; ?>

        <!-- Page script -->
        <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
        <script src="dist/js/inputtel.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#web_card_form').validate({
                    rules: {
                        filename: {
                            required: true,
                        },
                        full_name: {
                            required: true,
                        },
                        email: {
                            required: true,
                        },
                        phone: {
                            required: true,
                        },
                        photo: {
                            required: true,
                        }
                    }
                });
            });
        </script>

        <script>
            $(function () {

                //Colorpicker
                $('.my-colorpicker1').colorpicker()
                //color picker with addon
                $('.my-colorpicker2').colorpicker()

                $('.my-colorpicker2').on('colorpickerChange', function (event) {
                    $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
                });

            })
        </script>
</body>

</html>