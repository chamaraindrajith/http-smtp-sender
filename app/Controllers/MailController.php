<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class MailController extends BaseController
{
    public function index()
    {
        header('Content-Type: application/json');
        
        $test = false;
        $providers = ["default", "godaddy"];
        $data = [];
        
        $data['debug'] = 0; // 0, 1, SMTP::DEBUG_SERVER
        
        if ($test) {
            $data['server'] = "sg2plzcpnl487152.prod.sin2.secureserver.net";
            $data['port'] = "465";
            $data['username'] = "info@protechcomputers.lk";
            $data['password'] = "Codeintouch123";
            $data['security'] = "ssl";
            $data['sender_name'] = "ProTech Computers Test";
            $data['from'] = "info@protechcomputers.lk";
            $data['to'] = "chamaara.indrajith@outlook.com";
            $data['subject'] = "Test Subject";
            $data['body'] = "Test body body body body";
            $data['provider'] = "godaddy";
        } else {
            $data['server'] = $_GET["server"];
            $data['port'] = $_GET["port"];
            $data['username'] = $_GET["username"];
            $data['password'] = $_GET["password"];
            $data['security'] = $_GET["security"];
            $data['sender_name'] = $_GET["sender_name"];
            $data['from'] = $_GET["from"];
            $data['to'] = $_GET["to"];
            $data['subject'] = ($_GET["subject"] ? $_GET["subject"] : $_GET["from"]);
            $data['body'] = $_GET["body"];
            $data['provider'] = $_GET["provider"];
        }
        
        if (isset($data['provider'])) {
            if (!in_array($data['provider'], $providers)) {
                $data['provider'] = "default";
            }
        } else {
            $data['provider'] = "default";
        }

        return $this->provider($data);
    }
    
    public function provider($data) {
        switch ($data["provider"]) {
            case "godaddy":
                return $this->provider_godaddy($data);
                break;
            default:
                return $this->provider_default($data);
        }
    }
    
    public function provider_default($data) 
    {
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        
        $mail->Host       = $data['server'];
        $mail->SMTPDebug  = $data['debug'];
        $mail->SMTPAuth   = true;
        $mail->Port       = $data['port'];
        $mail->Username   = $data['username'];
        $mail->Password   = $data['password'];
        $mail->SMTPSecure = $data['security'];
        
        if (isset($data['sender_name'])) {
            $mail->setFrom($data['from'], $data['sender_name']);
        } else {
            $mail->setFrom($data['from']);
        }
        
        $to_email_list = json_decode(str_replace("'", '"', $data['to']));
        if (is_array($to_email_list)) {
            foreach ($to_email_list as $to_mail) {
                $mail->addAddress($to_mail);
            }
        } else {
            $mail->addAddress($data['to']);
        }
        
        $mail->isHTML(true);
        $mail->Subject = $data['subject'];
        $mail->Body    = $data['body'];
        $mail->AltBody = $data['body'];
        
        $response = [
            'status' => 'error',
            'message' => "Error: " . "Unknown error"
        ];
        try {
            if ($mail->send()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Email sent successfully!'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => "Error: mail->send(). " . $mail->ErrorInfo
                ];
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => "Error: main_function(). " . $mail->ErrorInfo
            ];
        }
        $response['provider'] = "default";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    public function provider_godaddy($data) {
        date_default_timezone_set('UTC');
        
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = $data['debug'];      
        $mail->SMTPSecure = "STARTTLS";
        $mail->SMTPAuth = false;  
        $mail->SMTPAutoTLS = false;
        $mail->Host = 'localhost';
        $mail->Port = 25;
        
        if (isset($data['sender_name'])) {
            $mail->setFrom($data['from'], $data['sender_name']);
            $mail->AddReplyTo($data['from'], $data['sender_name']);
        } else {
            $mail->setFrom($data['from']);
            $mail->AddReplyTo($data['from']);
        }
        
        if (isset($data['sender_name'])) {
            $mail->setFrom($data['from'], $data['sender_name']);
        } else {
            $mail->setFrom($data['from']);
        }

        $mail->Subject = $data['subject'];
        $mail->Body = $data['body'];
        $mail->AltBody = $data['body'];
        $mail->MsgHTML($data['body']);
        
        $to_email_list = json_decode(str_replace("'", '"', $data['to']));
        if (is_array($to_email_list)) {
            foreach ($to_email_list as $to_mail) {
                $mail->addAddress($to_mail);
            }
        } else {
            $mail->addAddress($data['to']);
        }
        
        try {
            if ($mail->send()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Email sent successfully!'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => "Error: mail->send(). " . $mail->ErrorInfo
                ];
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => "Error: main_function(). " . $mail->ErrorInfo
            ];
        }
        $response['provider'] = "godaddy";
        $response['data'] = $data;
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
