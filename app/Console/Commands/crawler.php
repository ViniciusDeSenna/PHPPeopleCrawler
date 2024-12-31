<?php

namespace App\Console\Commands;

use App\CrawlerUtil;
use App\Jobs\GetProfileData;
use Illuminate\Console\Command;
use DOMDocument;

class crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // URL base
        $url = "https://people.php.net/";
        $peoples = [];
        $page = 0;

        // para cada Page chama a Job que pucha os data do NickName
        // Valida se a pagina tem nicknames antes de enviar p job
        // Se nao tivar nicknames para de rodar

        // Coletando os data dos nicknames enquanto houver páginas
        do {
            $page++;
            $urlPage = $url . '?page=' . $page;

            $nicknames = [];
            $content = CrawlerUtil::getPageContent($urlPage);

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $table = $dom->getElementsByTagName("tbody")->item(0);
            if ($table) {
                $links = $table->getElementsByTagName("a");
            } else {
                break;
            }

            foreach($links as $link) {
                $nicknames[] = $link->textContent;
                GetProfileData::dispatch($url, $link->textContent);
            }

            if (empty($nickNames)) {
                break;
            }

        } while (!empty($nickNames));
    }
}
