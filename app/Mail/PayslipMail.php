<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public array $payslip) {}
    public function build(): self
    {
        $pdf = Pdf::loadView('payslips.pdf', ['payslip' => $this->payslip])->output();
        return $this->subject('Payslip: '.$this->payslip['period_start']->format('d M').' – '.$this->payslip['period_end']->format('d M Y'))
            ->view('emails.payslip')->attachData($pdf, 'payslip.pdf', ['mime' => 'application/pdf']);
    }
}
