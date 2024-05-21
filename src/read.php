<?php
include 'config/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['id']))
  die("Method not allowed. Check id parameter");

$db = getDbInstance();

$db->where("identifier", $_GET['id']);
$qrcode = $db->getOne("dynamic_qrcodes");
//var_dump($qrcode);die;
if (isset($qrcode)) {
  $data = array(
    'scan' => $db->inc(1)
  );
  $db->where("identifier", $_GET['id']);
  $db->update('dynamic_qrcodes', $data);

  if ($qrcode['state'] == 'enable') {
    echo '<meta http-equiv="refresh" content="0; URL=' . $qrcode['link'] . '" />';
    echo "<style>
        .content{
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .zoom-in-out-box {
            margin: 24px;
            width: 25%;
            animation: zoom-in-zoom-out 4s ease infinite, showing 2s ease backwards;
          }
          
          @keyframes zoom-in-zoom-out {
            0% {
              transform: scale(1, 1);
            }
            50% {
              transform: scale(1.5, 1.5);
            }
            100% {
              transform: scale(1, 1);
            }
          }

          @keyframes showing {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 100;
            }
          }
          </style>";
    echo '<div class="content">
          <img src="' . get_logo($qrcode['logo_company'], 600) . '" alt="Logo Prodex" class="zoom-in-out-box" />
        </div>'; // You can include a custom page to display during the redirect
  } else
    echo 'Disabled link';
} else {
  echo '<!DOCTYPE html>
    <html lang="en">
    <head>
      <base href="'.base_url().'/">
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>No encontrado</title>
      <!-- Boostrap -->
      <link rel="preconnect" href="https://cdn.jsdelivr.net">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- Custom Styles -->
      <link rel="stylesheet" href="dist/css/custom_web_card.css">
      <!--Font-->
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&display=swap" rel="stylesheet">
      
  </head>
    <body>
      <div class="container didact-gothic-regular">
      <div class="row"></div>
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-1">¡Lo sentimos!</h1>
            </div>
            <div class="col-12 text-center">
                <h1>El código QR ya no existe.</h1>
            </div>
        </div>
    </div>
    </body>
    </html>';
}
