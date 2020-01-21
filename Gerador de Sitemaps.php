<?php

// Dados para conexão com o banco de dados
define('HOST', '*****');
define('USER', '*****');
define('PASSWORD', '*****');
define('DB_NAME', '*****');
define('PORT', '*****');
define('HOME', 'https://seusite.com.br/');

try
{
    // Realiza a conexão com o banco de dados utilizando PDO
    $con = pg_connect("host=***** port=***** dbname=***** user=***** password=*****");
    //$con->exec("set names utf8");
}
catch (PDOException $e)
{
    echo 'Erro ao realizar a conexão com o banco de dados: ' . $e->getMessage();
}

// Realiza a consulta no banco de dados
$sql = "Insira sua query aqui";
$result = pg_query($con, $sql);
$rows = pg_fetch_all($result);

// Data e hora atual
$datetime = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo'));
// A linha abaixo me retornará uma data no seguinte formato: 2019-12-18T18:00:33-02:00
$date = $datetime->format(DateTime::ATOM); // ISO8601

// Gera o arquivo XML do sitemap
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    <url>
        <loc>'.HOME.'</loc>
        <lastmod>'.$date.'</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.00</priority>
    </url>';

foreach($rows as $row){
    $datetime = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo'));
    $date = $datetime->format(DateTime::ATOM);
    $xml .='
            <url>
                <loc>'.HOME.''.$row['username'].'/</loc>
                <lastmod>'.$date.'</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.85</priority>
            </url>';
}
$xml .= '
</urlset>';

// Tenta abrir o arquivo ou então cria caso não exista
$arquivo = fopen('/var/www/html/sitemap.xml', 'w');
if (fwrite($arquivo, $xml)) {
    echo "\n##### Arquivo sitemap.xml criado com sucesso #####\n\n";
} else {
    echo "Não foi possível criar o arquivo. Verifique as permissões do diretório.";
}
fclose($arquivo);

// Realiza compactação do arquivo sitemap para GZIP
$data = implode("", file("sitemap.xml"));
$gzdata = gzencode($data, 9);
$fp = fopen("sitemap.xml.gz", "w");
fwrite($fp, $gzdata);
fclose($fp);

// Envia para o Google o novo sitemap gerado
$urlSitemap = "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . HOME . "/";
// Arquivos a serem enviados
$Files = ['sitemap.xml', 'sitemap.xml.gz'];

// Envia os dois arquivos sitemap gerados para a URL do Google
foreach ($Files as $file) {
    $url = $urlSitemap . $file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}
?>
