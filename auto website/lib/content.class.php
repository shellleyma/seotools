<?php
abstract class collection{
    public function __construct($title){
        $this->title=$title;
        $this->useragent="";
        $this->referer="http://www.hitoy.org/";
        $this->timeout=10;
        $this->cookiefile="./cookie.txt";
    }
    abstract function geturl();
    protected function get_the_html(){
        $url=$this->geturl();
        if(function_exists("curl_init")){
            $ssl = substr($url, 0, 8) == "https://" ? true : false;
            $ch = curl_init();   
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_REFERER,$this->referer);
            curl_setopt($ch,CURLOPT_TIMEOUT,$this->timeout);
            curl_setopt($ch,CURLOPT_USERAGENT,$this->useragent);
            curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookiefile); 
            curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookiefile);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            if($ssl){
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            }

            if(!$data =  curl_exec($ch)){
                return file_get_contents($url);
            }
            curl_close($ch);
            return $data;
        }else{
            return file_get_contents($url);
        }
    }

}

class Yahoo extends collection{
    public function geturl($https=true,$count=20){
        if($https){
            return "https://search.yahoo.com/search?p=".urlencode($this->title)."&n=".$count;
        }else{
            return "http://search.yahoo.com/search?p=".urlencode($this->title)."&n=".$count;
        }
    }

    public function get_the_content($ttag="h2",$ctag="p"){
        $this->referer="https://www.yahoo.com/"; 
        $html=trim($this->get_the_html());
        if($html=="") return;
        preg_match_all("/<div class=\"dd algo[^>]*>([\s\S]*?)<\/div><\/li>/i",$html,$match);

        $content="";
        foreach($match[1] as $single){
            preg_match("/<h3[^>]*>([\s\S]*?)<\/h3>/i",$single,$m);            
            $title = strip_tags($m[1]);
            preg_match("/<div\sclass=\"compText aAbs\"[^>]*>([\s\S]*?)<\/div>/i",$single,$c);
            $p = strip_tags(preg_replace("/<span>[^<]+<\/span>/i","",$c[1]));
            $content .= "<$ttag>$title</$ttag>\r\n<$ctag>$p</$ctag>\r\n";
        }
        return $content;
    }
}


class Bing extends collection{
    public function geturl(){
        return "http://www.bing.com/search?q=".urlencode($this->title);
    }

    public function get_the_content($ttag="h2",$ctag="p"){
        $this->referer="http://www.bing.com/"; 
        $html=trim($this->get_the_html());
        if($html=="") return;
        preg_match_all("/<li class=\"b_algo\">([\s\S]*?)<\/li>/i",$html,$match);

        $content=""; 
        foreach($match[1] as $single){
            preg_match("/<h2[^>]*>([\s\S]*?)<\/h2>/i",$single,$m);
            $title=strip_tags($m[1]);
            preg_match("/<p>([\s\S]*?)<\/p>/i",$single,$c);
            if(!empty($c)){
                $p = strip_tags($c[1]);
            }else {
                $p = "";
            }
            if(strlen($p)>0){
                $content .= "<$ttag>$title</$ttag>\r\n<$ctag>$p</$ctag>\r\n";
            }
        }
        return $content;
    }
}
