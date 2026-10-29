<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $("button").click(function(){

                var value = $(this).val();
                var name = $("#name-file").val();

                $.ajax({
                    url: "/forcavws/script.php",
                    type: "POST",
                    data: "url="+value+"&name="+name,
                    dataType: "html"

                }).done(function(resposta) {
                    console.log(resposta);

                }).fail(function(jqXHR, textStatus ) {
                    console.log("Request failed: " + textStatus);

                }).always(function() {
                    console.log("completou");
                });        
                
              });
            });
        </script>
    </head>

</html>

<?php

ini_set('default_charset','UTF-8');

define('ABSPATH', dirname(__FILE__).'/');

require(APPPATH.'libraries/REST_Controller.php');    

require 'vendor/autoload.php';

use WebPConvert\WebPConvert;

set_time_limit(0);

class ProdutoBeagle extends REST_Controller{

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('ProdutoBeagle_model');
    }        

    function console_log( $data ){
      echo '<script>';
      echo 'console.log('. json_encode( $data ) .')';
      echo '</script>';
    }

    private function convertToWebP() {
        $data = $this->ProdutoBeagle_model->get();

        foreach($data as $item) {

            $url = 'http://192.168.0.11:2222/forcavws/images/' . $item['ImagemPequena'];

            $path_info = pathinfo($url);

            if ( array_key_exists( "extension", $path_info ) ) {  

                $i++;

                try{

                    $ext = '.' . $path_info['extension'];

                    $oldFile = 'F:/Ideal Brasil/Projetos/forcavws/images/' . $item['ImagemPequena'] ;
                    $newFile = 'tmp/' . $item['Ean'] . $ext;

                    if ( file_exists( $oldFile ) ) {
                        rename( $oldFile, $newFile ) ;

                        //$this->console_log('rename');

                        $source = $newFile; //__DIR__ . '/10120.png';
                        $destination = $source . '.webp';
                        $options = [];
                        WebPConvert::convert($source, $destination, $options);
                    }

                    //$i++;

                    //if ( $i > 20 ) { break; };
                } catch (Exception $e) {
                    //$this->console_log($e->getMessage());
                }finally{
                    ;
                }
            }

        }

        echo 'fim<br>';

        echo $i . '/' . count($data);
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    private function searchOnGoogle() {

        $url="https://www.googleapis.com/customsearch/v1?q=7898363310446&key=AIzaSyDlfPob0Gn3zHBFysIXzEuDEb-Eij5aSjM&cx=008944107520987424877%3A5st3rvrln1y&searchType=image";
        return file_get_contents( $url );

    }
        
    public function index_get() {

        $data = $this->searchOnGoogle();       
        $data = json_decode( $data, true );

         echo '<input type="text" name="name-file" id="name-file">';

        echo '<table><tr><th>Imagem</th><th>Seleção</th></tr>';

        foreach( $data['items'] as $arr => $key ) {
            $img = $key['link'];

            echo '<tr><td>';

            echo( strtoupper($key['title']) . "<br>" );
            echo( $key['displayLink'] . "<br>" );
            echo( "<img src='$img'>" . "<br>" );
            echo '</td><td>';

            echo '<button value="'.$img.'">ENVIAR</button>';            

            echo '</td></tr>';            

        }

        echo '</tr></table>';

    }
        
    public function by_get() {
       $this->index_get();
    }  
        
} 
?>