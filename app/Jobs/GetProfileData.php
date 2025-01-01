<?php

namespace App\Jobs;

use App\CrawlerUtil;
use App\Models\People;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Log;

class GetProfileData implements ShouldQueue
{
    use Queueable;

    public $url;
    public $nick;

    /**
     * Create a new job instance.
     */
    public function __construct($url, $nick)
    {
        $this->url = $url;
        $this->nick = $nick;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $content = CrawlerUtil::getPageContent($this->url . $this->nick);
            if (is_null($content)){
                return;
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $section = $dom->getElementsByTagName("section")->item(0);

            // Recuperando a imagem do perfil
            $img = str_replace('?s=460', '', $section->getElementsByTagName("img")->item(0)->getAttribute('src'));
            if (!preg_match('#^https?:#', $img)) {
                $img = 'https:' . $img;
            }

            $name = $dom->getElementsByTagName("h1")->item(0)->textContent;
            $nick = $dom->getElementsByTagName("h2")->item(0)->textContent;

            $ul = $section->getElementsByTagName("ul")->item(0);
            $li = $ul->getElementsByTagName("li")->item(0);

            $mail = trim($li->textContent);

            People::updateOrCreate(
                ['nick' => $nick],
                [
                    'profile_img_link' => $img,
                    'name' => $name,
                    'nick' => $nick,
                    'mail' => $mail,
                ]
            );

            //$filas = explode(',', env('DB_QUEUE'));
            DownloadImage::dispatch($this->nick, $img);
            //->onQueue($filas[array_rand($filas)]);

        }   catch(Exception $e) {
            Log::error('Erro no GetProfileData: ' . $e->getMessage());
        }
    }
}
