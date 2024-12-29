<?php
$page = 0;
$url = "https://people.php.net/";
$havePeople = true;
$nickNames = array();
$peoples = array();

// percorro enquanto tiver colaboradores na tela
while($havePeople) {
    $page++;

    $urlPage = $url . '?page=' . $page;

    $dom = new DOMDocument();
    @$dom->loadHTML(file_get_contents($urlPage));

    $table = $dom->getElementsByTagName("tbody")->item(0);
    $links = $table->getElementsByTagName("a");

    // Valida se ainda tem colaboradores na pagina, se não tiver finaliza a buca por nicknames
    if ($links->length <= 0) {
        $havePeople = false;
        continue;
    }

    // Pego os nicknames
    foreach($links as $link) {
        $nickNames[] = $link->textContent;
    }

    unset($urlPage, $dom, $table, $links);
}

// Acesso a página de cada nickname e coleto os dados
foreach($nickNames as $nick) {
    $dom = new DOMDocument();
    @$dom->loadHTML(file_get_contents($url . $nick));

    $section = $dom->getElementsByTagName("section")->item(0);

    $img = str_replace('?s=460', '', $section->getElementsByTagName("img")->item(0)->getAttribute('src'));
    if (!preg_match('#^https?:#', $img)) {
        $img = 'https:' . $img;
    }
    $name = $dom->getElementsByTagName("h1")->item(0)->textContent;
    $nickName = $dom->getElementsByTagName("h2")->item(0)->textContent;

    $ul = $section->getElementsByTagName("ul")->item(0);
    $li = $ul->getElementsByTagName("li")->item(0);

    $mail = trim($li->textContent);

    $peoples[] = [
        "profileImg" => $img,
        "name" => $name,
        "nickName" => $nickName,
        "mail" => $mail,
    ];

    // Baixo as imagens de perfil
    $imagename= basename($img);
    if(file_exists('./'.$imagename)){
        continue;
    } 
    if(!file_exists("./imagens")){
        mkdir("./imagens", 0777);
    }
    file_put_contents('./imagens/' . $nickName . '.jpg', file_get_contents($img)); 
}

var_dump($peoples);
echo 'Programa rodado com sucesso, ' . $page . ' páginas percorridas e ' . count($peoples) . ' contribuintes encontrados!';