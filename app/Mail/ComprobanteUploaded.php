<?php

namespace App\Mail;
    

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprobanteUploaded extends Mailable
{
    use Queueable, SerializesModels;

    public $comprobantes;
    public $user;

    public function __construct($comprobantes, $user)
    {
        $this->comprobantes = $comprobantes;
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.comprobante')
            ->with(['comprobantes' => $this->comprobantes, 'user' => $this->user]);
    }
}
