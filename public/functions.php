<?php
require_once '../vendor/autoload.php';

use SeinopSys\PostgresDb;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

const DB_NAME = "emailsender";
const HOST = "localhost";
const USERNAME = "frans";
const PASSWORD = "testPassword";

const SMTP_HOST = "";
const SMTP_USERNAME = "";
const SMTP_PASSWORD = "";
const SMTP_PORT = "";
const EMAIL_ORIGIN = "";
const EMAIL_ORIGIN_NAME = "";

function getRequest() {
    $entityBody = file_get_contents('php://input');
    $json = json_decode($entityBody);
    return $json;
}

function getUserFromToken(string $token, string $type) {
    try {
        $db = new PostgresDb(DB_NAME, HOST, USERNAME, PASSWORD);
        $db->getConnection();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    if($type == "user_token") {
        $user = $db->where("token", $token)->getOne("users");
    }

    return $user;
}

function getUserFromId(int $id) {
    try {
        $db = new PostgresDb(DB_NAME, HOST, USERNAME, PASSWORD);
        $db->getConnection();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $user = $db->where("id", $id)->getOne("users");

    return $user;
}

function getTopQueue(int $take) {
    try {
        $db = new PostgresDb(DB_NAME, HOST, USERNAME, PASSWORD);
        $db->getConnection();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $queue = $db->get("queue", $take, DB::$queue);
    if(!$queue) {
        return $queue;
    }

    return $queue;
}

function returnSuccess(array $data) {
    echo json_encode([
        "code" => 200,
        "status" => "success",
        "data" => $data,
    ]);
    exit();
}

function returnUnauthorized() {
    echo json_encode([
        "code" => 401,
        "status" => "unauthorized",
    ]);
    exit();
}

function returnBadRequest(string $message = "") {
    echo json_encode([
        "code" => 400,
        "status" => "bad_request",
        "message" => $message,
    ]);
    exit();
}

/**
 * Hit user's callback url
 */
function hitCallback(array $sent_mail, string $url) {
    $client = new GuzzleHttp\Client();
    $res = $client->post($url, [
       "body" => json_encode($sent_mail),
       "verify" => false,
    ]);

    echo $res->getBody();

    return true;
}

/**
 * Send email from parameters
 */
function sendEmail(string $recepient, string $subject, string $html, string $raw) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = SMTP_HOST;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = false;                                   // Enable SMTP authentication
        $mail->Username   = SMTP_USERNAME;                     // SMTP username
        $mail->Password   = SMTP_PASSWORD;                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = SMTP_PORT;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom(EMAIL_ORIGIN, EMAIL_ORIGIN_NAME);   // Add a recipient
        $mail->addAddress($recepient);

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $raw;

        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Email failed to be sent");
    }

    return true;
}

// if function changes any data then use this class
class DB {
    static $user = ["id", "username", "password", "url", "token", "secret"];
    static $queue = ["id", "raw", "html", "subject", "id_sender", "recepient"];
    static $sent_mail = ["id", "raw", "html", "subject", "id_sender", "recepient"];

    public static function insertUser(string $username, string $password, string $url) {
        $db = self::getConnection();

        $token = md5(time());
        $secret = md5("odeoemail" . time());

        $db->insert("users", [
            "username" => $username,
            "password" => $password,
            "url" => $url,
            "token" => $token,
            "secret" => $secret,
        ], self::$user);
    }

    public static function authUser(string $username, string $password) {
        $db = self::getConnection();

        $user = $db->where("username", $username)->where("password", md5($password))->getOne("users");
        if(!$user) {
            return $user;
        }

        // generate new token then update
        $newToken = md5($username . $password . time());

        $db->where('id', $user['id'])->update('users', ['token' => $newToken]);
    
        return $newToken;
    }

    public static function moveQueueToSent(array $queue) {
        $db = self::getConnection();
        
        foreach($queue as $q) {
            // remove from queue
            $sent_mail = $db->where('id', $q['id'])->delete('queue', DB::$queue);

            // if sent mail delete success
            if(count($sent_mail) > 0) {
                // get the user
                $user = getUserFromId($sent_mail['id_sender']);

                // if send mail success then hit user's callback url
                if(sendEmail($sent_mail['recepient'], $sent_mail['subject'], $sent_mail['html'], $sent_mail['raw'])) {
                    hitCallback($sent_mail, $user["url"]);
                }
            }

            // unset id so will be no duplicate keys
            unset($sent_mail['id']);

            // add to sent mail
            $db->insert('sent_mail', $sent_mail);
        }
    }

    public static function sendEmailToQueue($request, $id_sender) {
        $db = self::getConnection();
        
        $query = $db->insert('queue', [
            'recepient' => $request->data->recepient_email,
            'raw' => $request->data->raw, 
            'html' => $request->data->html, 
            'subject' => $request->data->subject,
            'id_sender' => $id_sender, 
        ], self::$queue);

        if(!$query) {
            throw new Exception($db->getLastError());
        }

        return $query;
    }

    public static function getConnection() {
        try {
            $db = new PostgresDb(DB_NAME, HOST, USERNAME, PASSWORD);
            $db->getConnection();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $db;
    }
}