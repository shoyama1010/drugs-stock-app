<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $employeeCode;
    public string $tempPin;

    public function __construct($name, $employeeCode, $tempPin)
    {
        $this->name = $name;
        $this->employeeCode = $employeeCode;
        $this->tempPin = $tempPin;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【DrugStore Stock】スタッフアカウント登録完了',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff_account_created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
