<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $title;
    public $image;
    public $url;


    public function __construct($title, $image = null , $url)
    {
        $this->title  =  $title;
        $this->image  =  $image;
        $this->url    =  $url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
