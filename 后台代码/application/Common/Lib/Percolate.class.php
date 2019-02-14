<?php

namespace Common\Lib;


/**
 * 过滤非法字符串类
 * time 2018.1.18
 * author xueweijian
 */
class Percolate
{
    /* Remove JS/CSS/IFRAME/FRAME 过滤JS/CSS/IFRAME/FRAME/XSS等恶意攻击代码(可安全使用)
   * Return string
   */
    public function cleanJsCss($html)
    {
        	$html=trim($html);
        	$html=preg_replace('/\0+/', '', $html);
        $html=preg_replace('/(\\\\0)+/', '', $html);
        $html=preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$html);
        $html=preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$html);
        $html=preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $html);
        $html=preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $html);
        	$html=str_replace(array('<?','?>'),array('<?','?>'),$html);
        $html=preg_replace('#\t+#',' ',$html);
        $scripts=array('javascript','vbscript','script','applet','alert','document','write','cookie','window');
        foreach($scripts as $script){
        	$temp_str="";
        	for($i=0;$i<strlen($script);$i++){
        		$temp_str.=substr($script,$i,1)."\s*";
        	}
        	$temp_str=substr($temp_str,0,-3);
        	$html=preg_replace('#'.$temp_str.'#s',$script,$html);
        	$html=preg_replace('#'.ucfirst($temp_str).'#s',ucfirst($script),$html);
        }
        $html=preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $html);
        $html=preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $html);
        $html=preg_replace("#<(script|xss).*?\>#si", "<\\1>", $html);
        $html=preg_replace('#(<[^>]*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#is',"\\1>",$html);
        //$html=preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "<\\1\\2\\3>", $html);
        $html=preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "<\\1\\2\\3>", $html);
        $html=preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2(\\3)", $html);
        $bad=array(
        'document.cookie'	=> '',
        'document.write'	=> '',
        'window.location'	=> '',
        "javascript\s*:"	=> '',
        "Redirect\s+302"	=> '',
        '<!--'				=> '<!--',
        '-->'				=> '-->'
        );
        foreach ($bad as $key=>$val){
        	$html=preg_replace("#".$key."#i",$val,$html);
        }
        return	$html;
    }
    //过滤html标签以及敏感字符
    public function cleanHtml($html)
    {
    	// return cleanYellow(htmlspecialchars($html));
        $this->cleanFilter($this->cleanYellow(htmlspecialchars($html)));

    }


    //过滤部分HTML标签
    public function cleanFilter($html)
    {
    	$html=trim($html);
    	$html=preg_replace("/<p[^>]*?>/is","<p>",$html);
    	$html=preg_replace("/<div[^>]*?>/is","<div>",$html);
    	$html=preg_replace("/<ul[^>]*?>/is","<ul>",$html);
    	$html=preg_replace("/<li[^>]*?>/is","<li>",$html);
    	$html=preg_replace("/<span[^>]*?/is","<span>",$html);
    	$html=preg_replace("/<a[^>]*?>(.*)?<\/a>/is","\${1}",$html);
    	$html=preg_replace("/<table[^>]*?>/is","<table>",$html);
    	$html=preg_replace("/<tr[^>]*?>/is","<tr>",$html);
    	$html=preg_replace("/<td[^>]*?>/is","<td>",$html);
    	$html=preg_replace("/<ol[^>]*?>/is","<ol>",$html);
    	$html=preg_replace("/<form[^>]*?>/is","",$html);
    	$html=preg_replace("/<input[^>]*?>/is","",$html);
    	// return $html;
        $this->cleanYellow($html);

    }
    //过滤非法的敏感字符串
    public function cleanYellow($txt)
    {
    	$txt=str_replace(
    	array("黄色","性爱","做爱","我日","我草","我靠","尻","共产党","胡锦涛","毛泽东",
    	"政府","中央","研究生考试","性生活","色情","情色","我考","麻痹","妈的","阴道",
    	"淫","奸","阴部","爱液","阴液","臀","色诱","煞笔","傻比","阴茎","法轮功","性交","阴毛","江泽民"),
    	array("*1*","*2*","*3*","*4*","*5*","*6*","*7*","*8*","*9*","*10*",
    	"*11*","*12*","*13*","*14*","*15*","*16*","*17*","*18*","*19*","*20*",
    	"*21*","*22*","*23*","*24*","*25*","*26*","*27*","*28*","*29*","*30*","*31*","*32*","*33*","*34*"),
    	$txt);
    	// return $txt;
        $this->cleanAll($txt);
    }
    //过滤敏感字符串以及恶意代码
    public function cleanAll($html)
    {
    	return $this->cleanYellow($this->cleanJsCss($html));
    }
}
