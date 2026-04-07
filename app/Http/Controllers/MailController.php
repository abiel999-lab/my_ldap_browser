<?php

namespace App\Http\Controllers;

use App\Mail\ExamplePetraMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Summary of send
     *
     * @return string
     */
    public function send(Request $request)
    {
        /**
         * Example of sending mail using Petra Mail
         *
         * @see \App\Mail\ExamplePetraMail
         * Copy the ExamplePetraMail class to create your own mail class
         */
        Mail::to('kevinlinuhung@petra.ac.id')->queue(new ExamplePetraMail(template_params: ['name' => 'Kevin Linuhung']));
        // Mail::to('kevinlinuhung@petra.ac.id')->send(new ExampleSendMail('DEFAULT_MAIL_TEMPLATE', ['name' => 'Kevin Linuhung']));

        return 'Mail sent';
    }
}
