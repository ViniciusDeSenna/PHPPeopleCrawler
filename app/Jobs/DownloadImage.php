<?php

namespace App\Jobs;

use App\Models\People;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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
        try {
            $imagePath = 'imagens/' . $this->nick . '.jpg'; // Definindo o caminho do arquivo dentro do storage
    
            // Verificando se a pasta existe, caso contrário, cria
            if (!Storage::exists('imagens')) {
                Storage::makeDirectory('imagens');
            }
    
            // Verificando se a imagem já foi baixada
            if (!Storage::exists($imagePath)) {
                // Obtendo o conteúdo da imagem da URL fornecida
                $imageContent = file_get_contents($this->img);
    
                if ($imageContent) {
                    // Armazenando o arquivo no sistema de arquivos
                    Storage::put($imagePath, $imageContent);
                }
            }

            People::query()
                ->where('nick', '=', $this->nick)
                ->update(['profile_img_path' => $imagePath]);
                
        } catch (Exception $e) {
            Log::error('Erro no DownloadImage: ' . $e->getMessage());
        }
    }
}
