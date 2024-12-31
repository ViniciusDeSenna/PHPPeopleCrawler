<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DownloadImage implements ShouldQueue
{
    use Queueable;

    public $nick;
    public $img;

    /**
     * Create a new job instance.
     */
    public function __construct($nick, $img)
    {
        $this->nick = $nick;
        $this->img = $img;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imagename = basename($this->img);
        $imagePath = './imagens/' . $this->nick . '.jpg';

        if (!file_exists("./imagens")) {
            mkdir("./imagens", 0777, true);
        }

        if (!file_exists($imagePath)) {
            $imageContent = file_get_contents($this->img);
            if ($imageContent) {
                file_put_contents($imagePath, $imageContent);
            }
        }
    }
}
