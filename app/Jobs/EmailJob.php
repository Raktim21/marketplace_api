<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use PHPMailer\PHPMailer\PHPMailer;

class EmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
    */
    private $email;
    private $subject;
    private $name;
    private $body;

    public function __construct($email, $subject, $name, $body)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $mail = new PHPMailer(true);
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST_2', 'smtp.mailgun.org');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME_2');
        $mail->Password   = env('MAIL_PASSWORD_2');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION_2', 'ssl');
        $mail->Port       = env('MAIL_PORT_2', 465);
        // $mail->SMTPDebug  = 2; // Enable verbose debug output
        // $mail->Debugoutput = function($str, $level) {
        //     Log::info("PHPMailer: $str");
        // };
        $mail->setFrom('noreply@sellhub.io', $this->subject);
        $mail->addAddress($this->email, $this->name);
        $mail->isHTML(true);
        $mail->Subject = $this->subject;
        $mail->Body = $this->body;

        $mail->send();
    }
}
