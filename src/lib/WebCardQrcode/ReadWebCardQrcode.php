<?php
require_once 'config/config.php';
require_once BASE_PATH . '/lib/vCard/vCard.php';

class ReadWebCardQrcode {
    /**
     *
     */
    public function __construct() {
    }

    /**
     *
     */
    public function __destruct() {
    }

    public function getQrcode($id) {
        $db = getDbInstance();

        $db->where("identifier", $_GET['id']);
        $web_card_qrcode = $db->getOne("web_card_qrcodes");
        $web_card_qrcode["content_decode"] = !empty($web_card_qrcode["content"]) ? json_decode($web_card_qrcode["content"], true) : "";
        return $web_card_qrcode;
    }

    public function getVcard($qrcode) {
        $vcard = new vCard;
        $vcard->name($qrcode["full_name"]);
        $vcard->organization("PRODEX"); 
        $vcard->role($qrcode["role"]); 
        $vcard->photo($qrcode["photo"]); 
        $vcard->cellPhone($qrcode["phone"]); 
        $vcard->workPhone($qrcode["work_phone"]);
        $vcard->address($qrcode["address"], $qrcode["city"], $qrcode["post_code"], $qrcode["state"], $qrcode["country"]); 
        $vcard->email($qrcode["email"]); 
        $vcard->url($qrcode["website"]); 
        $vcard->note($qrcode["note"]);
        $vcard->revision($qrcode["created_at"]);
        $vcard->create();
        return $vcard->get();
    }

    public function updateScanQrcode($id) {
        $db = getDbInstance();

        $data = array(
            'scan' => $db->inc(1)
        );
        $db->where("identifier", $_GET['id']);
        $db->update('web_card_qrcodes', $data);
    }
    
    /**
     * Add qr code
     * Check out http://goqr.me/api/ for more information
     * We save the file obtained with the chosen name and in the selected folder
     * We save into db the url of qrcode image
     */
    public function addQrcode($input_data) {
        if($input_data['id_owner'] != "")
            $data_to_db['id_owner'] = $input_data['id_owner'];
        else
            $data_to_db['id_owner'] = NULL;

        $data_to_db['filename'] = htmlspecialchars($input_data['filename'], ENT_QUOTES, 'UTF-8');
        $data_to_db['created_at'] = date('Y-m-d H:i:s');
        $data_to_db['content'] = json_encode([
            "full_name" => $_POST['full_name'], "nickname" => $_POST['nickname'], "email" => $_POST['email'], 
            "website" => $_POST['website'], "phone" => $_POST['phone'],  
            "work_phone" => $_POST['work_phone'], "role" => $_POST['role'], 
            "note" => $_POST['note'], "photo" => $_POST['photo'], 
            "address" => $_POST['address'], "city" => $_POST['city'], "post_code" => $_POST['post_code'], 
            "state" => $_POST['state'], "country" => $_POST['country']
        ]);
        $data_to_db['created_by'] = $_SESSION['user_id'];
        $data_to_db['format'] = $input_data['format'];
        $data_to_db['identifier'] = randomString(rand(5,8));
        $data_to_db['qrcode'] = $data_to_db['filename'].'.'.$data_to_db['format'];

        $data_to_qrcode = READ_WEB_CARD_PATH.$data_to_db['identifier'];
        
        $this->qrcode_instance->addQrcode($input_data, $data_to_db, $data_to_qrcode);
    }
    
    /**
     * Edit qr code
     * 
     */
    public function editQrcode($input_data) {
        if($input_data['id_owner'] != "")
            $data_to_db['id_owner'] = $input_data['id_owner'];
        else
            $data_to_db['id_owner'] = NULL;
        $data_to_db['filename'] = htmlspecialchars($input_data['filename'], ENT_QUOTES, 'UTF-8');
        $data_to_db['created_at'] = date('Y-m-d H:i:s');
        $data_to_db['content'] = json_encode([
            "full_name" => $_POST['full_name'], "nickname" => $_POST['nickname'], "email" => $_POST['email'], 
            "website" => $_POST['website'], "phone" => $_POST['phone'],  
            "work_phone" => $_POST['work_phone'], "role" => $_POST['role'], 
            "note" => $_POST['note'], "photo" => $_POST['photo'], 
            "address" => $_POST['address'], "city" => $_POST['city'], "post_code" => $_POST['post_code'], 
            "state" => $_POST['state'], "country" => $_POST['country']
        ]);
        $data_to_db['state'] = $input_data['state'];

        $this->qrcode_instance->editQrcode($input_data, $data_to_db);
    }

    
    /**
     * Delete qr code
     * 
     */
    public function deleteQrcode($id) {
        if($_SESSION['type'] === "super") {
            $this->qrcode_instance->deleteQrcode($id);
        } else if ($_SESSION['type'] === "admin") {
            $qrcode = $this->getQrcode($id);

            if(!isset($qrcode["id_owner"]))
                $this->failure("You cannot delete this qrcode");

            require_once BASE_PATH . '/lib/Users/Users.php';
            $users = new Users();
            $user = $users->getUser($_SESSION['user_id']);

            if($user["id"] === $qrcode["id_owner"])
                $this->qrcode_instance->deleteQrcode($id);
            else
                $this->failure("You cannot delete this qrcode because it's of another user");
        }
    }


    /**
     * Flash message Failure process
     */
    private function failure($message) {
        $_SESSION['failure'] = $message;
        header('Location: web_card_qrcodes.php');
    	exit();
    }
    
    /**
     * Flash message Success process
     */
    private function success($message) {
        $_SESSION['success'] = $message;
        header('Location: web_card_qrcodes.php');
    	exit();
    }
    
    /**
     * Flash message Info process
     */
    private function info($message) {
        $_SESSION['info'] = $message;
        header('Location: web_card_qrcodes.php');
    	exit();
    }

    public function debug($data) {
        echo '<pre>' . var_export($data, true) . '</pre>';
        exit();
    }
}
?>
