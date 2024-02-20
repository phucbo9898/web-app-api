<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChangeEmailMail extends Mailable
{
    use Queueable, SerializesModels;
    public $input;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('[003] Build request change email for user: '. $this->input['new_email']);
        return $this->subject('Send mail change email by user')
            ->text('email.verifyMail', ['input' => $this->input]);
    }
}
