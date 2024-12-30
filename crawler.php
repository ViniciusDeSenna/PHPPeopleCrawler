<?php

function logError($message) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Função para buscar o conteúdo da página
function getPageContent($url) {
    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0"
        ]
    ];
    $context = stream_context_create($options);
    $content = file_get_contents($url, false, $context);
    if (!$content) {
        return null;
    }
    return $content;
}

// Função para extrair os nicknames da página de listagem
function getNicknamesFromPage($url) {
    $nicknames = [];
    $content = getPageContent($url);

    $dom = new DOMDocument();
    @$dom->loadHTML($content);
    if (!$dom) {
        die("Failed to load HTML content for URL: " . $url);
    }

    $table = $dom->getElementsByTagName("tbody")->item(0);
    if ($table) {
        $links = $table->getElementsByTagName("a");
    } else {
        echo "No table found for URL: $url";
        return [];
    }

    foreach($links as $link) {
        $nicknames[] = $link->textContent;
    }

    return $nicknames;
}

// Função para extrair os dados do perfil de cada colaborador
function getProfileData($nickName, $url) {
    $profile = [];
    $content = getPageContent($url . $nickName);
    if (is_null($content)){
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($content);
    if (!$dom) {
        die("Failed to load HTML content for URL: " . $url);
    }

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

    $profile = [
        "profileImg" => $img,
        "name" => $name,
        "nickName" => $nick,
        "mail" => $mail,
    ];

    return $profile;
}

// Função para baixar as imagens de perfil
function downloadImage($img, $nickName) {
    $imagename = basename($img);
    $imagePath = './imagens/' . $nickName . '.jpg';

    if (!file_exists("./imagens")) {
        mkdir("./imagens", 0777, true);
    }

    if (!file_exists($imagePath)) {
        $imageContent = file_get_contents($img);
        if ($imageContent) {
            file_put_contents($imagePath, $imageContent);
        } else {
            echo "Erro ao baixar a imagem para: $nickName";
        }
    }
}

// URL base
$url = "https://people.php.net/";
$peoples = [];
$page = 0;

// Coletando os nicknames enquanto houver páginas
do {
    $page++;
    $urlPage = $url . '?page=' . $page;

    $nickNames = getNicknamesFromPage($urlPage);

    if (empty($nickNames)) {
        break;
    }

    // Para cada nickname, colete as informações
    foreach($nickNames as $nick) {
        $profileData = getProfileData($nick, $url);
        if (is_null($profileData)) {
            continue;
        }
        
        $peoples[] = $profileData;

        // Baixar a imagem do perfil
        downloadImage($profileData['profileImg'], $nick);
    }

} while (!empty($nickNames));

// Exibe os resultados
var_dump($peoples);
echo 'Programa rodado com sucesso, ' . $page . ' páginas percorridas e ' . count($peoples) . ' contribuintes encontrados!';

?>