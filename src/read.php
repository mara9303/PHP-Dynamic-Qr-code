<?php
include 'config/config.php';

if($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['id']))
    die("Method not allowed. Check id parameter");

$db = getDbInstance();

$db->where("identifier", $_GET['id']);
$qrcode = $db->getOne("dynamic_qrcodes");

$data = array (
    'scan' => $db->inc(1)
);
$db->where("identifier", $_GET['id']);
$db->update ('dynamic_qrcodes', $data);
    
    if($qrcode['state'] == 'enable'){
        //echo '<meta http-equiv="refresh" content="0; URL='.$qrcode['link'].'" />';
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
          <img src="'.LOGO_QR.'" alt="Logo Prodex" class="zoom-in-out-box" />
        </div>'; // You can include a custom page to display during the redirect
    }
    else
        echo 'Disabled link';
?>
