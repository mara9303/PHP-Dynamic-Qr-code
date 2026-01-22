<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/lib/Logo/Logo.php';

$db = getDbInstance();
$logoInstance = new Logo();

if ($_SESSION['type'] !== 'super') {
    $logoInstance->failure('Only a "super admin" account can access the logos page', 'Location: index.php');
}

$select = array('id', 'name', 'filename', 'state', 'created_at', 'updated_at');
$search_fields = array('name');
require_once BASE_PATH . '/includes/search_order.php';
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 1;
$db->pageLimit = 15;
$rows = $db->arraybuilder()->paginate('logos', $page, $select);
$total_pages = $db->totalPages;
?>

<!DOCTYPE html>
<html lang="en">
    <title>Qrcode Generator - Logos</title>
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
            <h1 class="m-0 text-dark">Logos</h1>
          </div><!-- /.col -->
          
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <a href="logo_crud.php" class="btn btn-success"><i class="fa fa-plus"></i> Add new</a>
                </li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div><!-- /.content-header -->
    
    <!-- Flash message-->
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    <!-- /.Flash message-->
    
    <!-- Filters -->
    <?php   $options = $logoInstance->setOrderingValues();
            include BASE_PATH . '/forms/filters.php'; ?>
    <!-- /.Filters -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
          
            <!-- Table -->
            <?php include BASE_PATH . '/forms/table_logos_crud.php'; ?>
            <!-- /.Table -->

        </div><!-- /.container-fluid -->
    </section>
  </div><!-- /.content-wrapper -->

    <!-- Footer and scripts -->
    <?php include './includes/footer.php'; ?>
    <!-- /.Footer and scripts -->
</body>
</html>
