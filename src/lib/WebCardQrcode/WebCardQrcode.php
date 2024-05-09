<?php
require_once 'config/config.php';
require_once BASE_PATH . '/lib/Qrcode/Qrcode.php';


class WebCardQrcode {
    private Qrcode $qrcode_instance;
    /**
     *
     */
    public function __construct() {
        $this->qrcode_instance = new Qrcode("web_card");
    }

    /**
     *
     */
    public function __destruct() {
    }
    
    /**
     * Set friendly columns names to order tables entries
     * N.B. This function is called to generate the "list all" table
     */
    public function setOrderingValues()
    {
        $ordering = [
            'id' => 'ID',
            'id_owner' => 'Owner',
            'filename' => 'File Name',
            'identifier' => 'Identifier',
            'qrcode' => 'Qr Code',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        ];

        return $ordering;
    }

    public function getQrcode($id) {
        $web_card_qrcode = $this->qrcode_instance->getQrcode($id);
        $content = !empty($web_card_qrcode["content"]) ? json_decode($web_card_qrcode["content"], true) : "";
        $result = !empty($content) ? array_merge($web_card_qrcode, $content) : $web_card_qrcode;
        return $result;
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
