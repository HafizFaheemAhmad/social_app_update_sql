<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\verifyemail;
use Mail;

use App\Http\Controllers\API\RegisterController;


class SendEmailJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $send_mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $details, $email, $password;
    public function __construct($details, $email)
    {
        $this->details = $details;
        $this->email = $email;
     }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new verifyemail(RegisterController::$detail);

        Mail::to($this->email)->send(new verifyemail($this->details));
    }
}
