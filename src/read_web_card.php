<?php
include 'config/config.php';
require_once BASE_PATH . '/lib/WebCardQrcode/ReadWebCardQrcode.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['id']))
    die("Method not allowed. Check id parameter");

$web_card_qrcode_instance = new ReadWebCardQrcode();
$qrcode = $web_card_qrcode_instance->getQrcode($_GET['id']);
if (array_key_exists("identifier", $qrcode)) {
    $qrcode["content_decode"]["created_at"] = $qrcode["created_at"];
    $web_card_content = parse_web_card_object($qrcode["content_decode"]);
    //echo var_dump($web_card_content);die;
    $web_card_qrcode_instance->updateScanQrcode($_GET['id']);
    $vCard = $web_card_qrcode_instance->getVcard($web_card_content);
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include './includes/web_card/head.php'; ?>

<body class="didact-gothic-regular">
    <div class="container d-flex justify-content-center">
        <div class="d-flex align-items-center">
            <img class="logos" src="dist/img/prodex-200.png" alt="Prodex logo" width="200" heigth="133">
        </div>
        <div class="d-flex align-items-center">
            <img class="logos" src="dist/img/proplax-200.png" alt="Proplax logo" width="200" heigth="133">
        </div>
    </div>
    <hr>
    <?php
    if (array_key_exists("identifier", $qrcode)) {
        if ($qrcode['state'] == 'enable') { ?>
            <div class="container d-flex flex-column align-items-center my-3 my-sm-5">
                <div class="card p-3 py-4">
                    <div class="text-center">
                        <?php if (!empty($web_card_content["photo"])) { ?>
                            <img src="<?= $web_card_content["photo"] ?>" width="100" class="rounded-circle"
                                alt="<?= read_key_array($web_card_content, "full_name", "") ?>">
                        <?php } ?>
                        <h1 class="mt-2"><?= read_key_array($web_card_content, "full_name", "") ?></h1>
                        <span class="mt-1 clearfix"><?= read_key_array($web_card_content, "role", "") ?></span>

                        <div class="row mt-3 mb-3">
                            <?php if (!empty($web_card_content["email"])) { ?>
                                <div class="col-12 my-2">
                                    <a href="mailto:<?= read_key_array($web_card_content, "email", "") ?>?Subject=Contacto%20desde%20QR"
                                        title="Enviar email a <?= read_key_array($web_card_content, "email", "") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fas fa-envelope fa-2x"></i>
                                            </div>
                                            <div class="col d-flex justify-content-start">
                                                <span
                                                    class="text-start"><?= read_key_array($web_card_content, "email", "") ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                            <div class="col-12 my-2">
                                <a href="tel:<?= read_key_array($web_card_content, "phone", "") ?>"
                                    title="Llamar a <?= read_key_array($web_card_content, "phone", "") ?>">
                                    <div class="row">
                                        <div class="col-2">
                                            <i class="fas fa-mobile fa-2x"></i>
                                        </div>
                                        <div class="col d-flex justify-content-start">
                                            <span
                                                class="text-start"><?= read_key_array($web_card_content, "phone", "") ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php if (!empty($web_card_content["work_phone"])) { ?>
                                <div class="col-12 my-2">
                                    <a href="tel:<?= read_key_array($web_card_content, "work_phone", "") ?>"
                                        title="Llamar a <?= read_key_array($web_card_content, "work_phone", "") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fas fa-phone fa-2x"></i>
                                            </div>
                                            <div class="col d-flex justify-content-start">
                                                <span
                                                    class="text-start"><?= read_key_array($web_card_content, "work_phone", "") ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if (!empty($web_card_content["website"])) { ?>
                                <div class="col-12 my-2">
                                    <a href="<?= read_key_array($web_card_content, "website", "") ?>"
                                        title="<?= read_key_array($web_card_content, "website", "") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fas fa-globe fa-2x"></i>
                                            </div>
                                            <div class="col d-flex justify-content-start">
                                                <span
                                                    class="text-start"><?= read_key_array($web_card_content, "website", "") ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if (!empty(read_key_array($web_card_content, "post_code", "") . read_key_array($web_card_content, "address", "") . read_key_array($web_card_content, "city", "") . read_key_array($web_card_content, "state", "") . read_key_array($web_card_content, "country", ""))) { ?>
                                <div class="col-12 my-2">
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(read_key_array($web_card_content, "post_code", "") . ", " . read_key_array($web_card_content, "address", "") . ", " . read_key_array($web_card_content, "city", "") . ", " . read_key_array($web_card_content, "state", "") . ", " . read_key_array($web_card_content, "country", "")) ?>"
                                        target="_blank"
                                        title="<?= read_key_array($web_card_content, "post_code", "") ?>, <?= read_key_array($web_card_content, "address", "") ?>, <?= read_key_array($web_card_content, "city", "") ?>, <?= read_key_array($web_card_content, "state", "") ?>, <?= read_key_array($web_card_content, "country", "") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fas fa-map-marker-alt fa-2x"></i>
                                            </div>
                                            <div class="col d-flex justify-content-start">
                                                <span class="text-start"><?= read_key_array($web_card_content, "post_code", "") ?>,
                                                    <?= read_key_array($web_card_content, "address", "") ?>,
                                                    <?= read_key_array($web_card_content, "city", "") ?>,
                                                    <?= read_key_array($web_card_content, "state", "") ?>,
                                                    <?= read_key_array($web_card_content, "country", "") ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <hr class="line">

                        <small class="mt-4"><?= read_key_array($web_card_content, "note", "") ?></small>
                    </div>
                    <div class="buttons">
                        <div class="dropup-center dropup actions sharing">
                            <button class="btn btn-danger dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item fb-share-button" target="_blank" title="Facebook"
                                        href="https://www.facebook.com/sharer/sharer.php?u=<?= current_url() ?>">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank" title="Whatsapp"
                                        href="https://api.whatsapp.com/send?text=Esta es mi tarjeta de presentación: <?= current_url() ?>">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank" title="Email"
                                        href="mailto:?subject=<?= rawurlencode("Contacto " . read_key_array($web_card_content, "full_name", "")) ?>&body=<?= rawurlencode("Esta es mi tarjeta de presentación " . current_url()) ?>">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                </li>
                                <li>
                                    <button id="btn-copy-link" class="dropdown-item" title="Link"
                                        data-href="<?= current_url() ?>">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="dropstart dropup actions download">
                            <button class="btn btn-danger dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-user-plus"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button id="btn-vCard" class="dropdown-item" data-vcard="<?= htmlspecialchars($vCard) ?>"><i
                                            class="fas fa-save"></i></button></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div id="copyNotification" class="toast position-fixed bottom-0 mb-4 rounded-4" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="toast-header rounded-top-4">
                    <strong class="me-auto">Qrcode Generator</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Link copiado!!
                </div>
            </div>
            <?php include_once './includes/web_card/sharing_buttons.php'; ?>
        <?php } else {
            echo 'Disabled link';
        }
        ?>
    <?php } else { ?>
        <div class="container d-flex justify-content-center">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-1">¡Lo sentimos!</h1>
                </div>
                <div class="col-12 text-center">
                    <h1>La tarjeta de presentacipon ya no existe.</h1>
                </div>
            </div>


        </div>
    <?php } ?>
</body>
<?php include './includes/web_card/footer.php'; ?>

</html>