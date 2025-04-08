<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function build()
    {
        return $this->subject('Verify Your Student Account')
            ->markdown('emails.student_verification')
            ->with([
                'verificationUrl' => url('/api/verify-email?token='.$this->student->verification_token),
            ]);
    }
}