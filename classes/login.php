<?php
require_once "./vendor/autoload.php";
require_once "db.php";

use \Firebase\JWT\JWT;

header('Content-Type: application/json ');

class Login {

    private $db;
    private $connect;

    private $email;
    private $password;

    //JWT data's
    private $key = 'YOUR_SECRET_KEY';
    private $issuer = 'root';
    private $audience = 'THE_AUDIENCE';
    private $issue_date;
    private $notbefore;
    private $expire;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function api()
    {
        $this->connect = $this->db->connectDB('localhost', 'root', 'icore_auth_api', ''); 
        //conn = $this->connect->connectDB('localhost', 'root', 'icore_uth_api', '');
        //if(!$conn) echo "error";
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $data = json_decode(file_get_contents('php://input'));

            $this->email = htmlspecialchars(trim($data->email));
            $this->password = htmlspecialchars(trim($data->password));

            $query = 'SELECT * FROM users where email = :email Limit 1';
            $stmt = $this->connect->prepare('SELECT * FROM users where email = :email Limit 1');
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ( $rowCount > 0 ) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                //echo json_encode(['id' => $row['id']]);
                $id = $row['id'];
                $password = $row['password'];

                $hash_password = password_verify($this->password, $password);
                if($hash_password === true) {
                    //echo $row['email'];
                    $this->issue_date = time();
                    $this->notbefore = $this->issue_date + 60;
                    $this->expire = $this->issue_date + 60;
                    $token = array(
                            'iss' => $this->issuer,
                            'aud' => $this->audience,
                            'iat' => $this->issue_date,
                            'nbf' => $this->notbefore,
                            'exp' => $this->expire,
                            'data' => array(
                                'id' => $id,
                            )
                        );
                    $jwt = JWT::encode($token, $this->key);
                    echo json_encode(array(
                        'message' => 'Logged in successfully',
                        'jwt' => $jwt,
                    ));
                    //echo JWT::decode($jwt, $key, array('HS256'));
                } else {
                    http_response_code(401);

                    echo json_encode(array(
                        'message' => 'Incorrect Password'
                    ));
                }
            } else {
                http_response_code(401);

                echo json_encode(array(
                    'message' => 'No user has registerd with this Email'
                ));
            }
            
        } else {
            echo json_encode(array(
                'message' => 'This API Only supports POST method'
            ));
        }
    }

}