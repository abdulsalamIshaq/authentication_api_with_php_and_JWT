<?php
require_once "./vendor/autoload.php";
require_once "db.php";

use \Firebase\JWT\JWT;

header('Content-Type: application/json ');

class Register {
    private $name;
    private $email;
    private $password;

    private $db;
    private $connect;
    
    public function __construct()
    {
        //instantiate a new DB class
        $this->db = new DB();      
    }

    public function api() {
        //connect to the db
        $this->connect = $this->db->connectDB('localhost', 'root', 'icore_auth_api', ''); 
        //check if request method is post
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            //get our post body as json
            $data = json_decode(file_get_contents('php://input'));
            //print_r($data);
            //validate our form data's
            $this->name = filter_var(trim($data->name), FILTER_SANITIZE_STRING);
            $this->email = trim($data->email);
            $this->password = password_hash(trim($data->password), PASSWORD_DEFAULT);

            //validate email to check if user already exist
            $this->validateEmail($this->email);

            //insert new user into db
            $query = 'INSERT INTO users(name, email, password) VALUES(:name, :email, :password)';
            $stmt = $this->connect->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            if( $stmt->execute() ) {
                http_response_code(200);

                echo json_encode(array(
                    'message' => 'Registered Successfully'
                ));
            }else{
                http_response_code(500);
                echo json_encode(array(
                    'message' => 'Registeration was not successfully please try again'
                ));
            }

        } else {
            echo json_encode(array(
                'message' => 'This API Only supports POST method'
            ));
        }
    }
    
    public function validateEmail( String $email ) {
        //validate email
        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ){
            http_response_code(401);

            echo json_encode(array(
                'message' => 'Invalid mail format'
            ));
            exit;
        }
        
        $query = 'SELECT * FROM users WHERE email = :email LIMIT 0,1';
        $stmt = $this->connect->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        if ($rowCount) {
            http_response_code(401);

            echo json_encode(array(
                'message' => 'Someone already registered with the Email'
            ));
            exit;
        }
    }
}