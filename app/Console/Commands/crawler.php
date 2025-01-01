<?php

namespace App\Console\Commands;

use App\CrawlerUtil;
use App\Jobs\GetProfileData;
use Illuminate\Console\Command;
use DOMDocument;
use Illuminate\Support\Facades\Log;

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
        Log::info('Crawler Iniciado!');

        // URL base
        $url = "https://people.php.net/";
        $page = 0;

        // para cada Page chama a Job que pucha os data do NickName
        // Valida se a pagina tem nicknames antes de enviar p job
        // Se nao tivar nicknames para de rodar

        // Coletando os data dos nicknames enquanto houver páginas
        do {
            $page++;
            $urlPage = $url . '?page=' . $page;

            $nickNames = [];
            $content = CrawlerUtil::getPageContent($urlPage);

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $table = $dom->getElementsByTagName("tbody")->item(0);
            if ($table) {
                $links = $table->getElementsByTagName("a");
            } else {
                break;
            }

            //$filas = explode(',', env('DB_QUEUE'));
            foreach($links as $index => $item) {
                //$fila = $filas[$index % count($filas)];
                $nickNames[] = $item->textContent;
                GetProfileData::dispatch($url, $item->textContent);
                //->onQueue($fila);
            }

            if (empty($nickNames)) {
                break;
            }

        } while (!empty($nickNames));
        
        Log::info('Crawler Finalizado!');
    }
}
