 <?php
/**
 * En este archivo se detallará el controlador del análisis del código
 * 
 * @author iago
 */

//comprueba que los directorios sean los de Directories.conf
function comprobarDirectorio(){
    $directoriosConf=file('../conf/Directories.conf',FILE_IGNORE_NEW_LINES); //guarda en un array los directorios a comprobar
    for($i=0;$i<count($directoriosConf);$i++){
        if(!@scandir('../'.$directoriosConf[$i])){ //si no existe una dir se guarda en el array de errores
            $str=explode('/',$directoriosConf[$i],2); //spq
            $toret[]=$str[1];
        }
    }
    return $toret; //devuelve todas las carpetas q NO existen (y deberian)
}

//comprueba que los archivos tengan nombres permitidos en File.conf
function comprobarFileName(){
    $fileConf=file('../conf/Files.conf',FILE_IGNORE_NEW_LINES); //guarda en un array los nombres a comprobar
    for($i=0;$i<count($fileConf);$i++){
        $dirYName=explode(':',$fileConf[$i],2); //array: 0 dir, 1 name
        if($files=@scandir('../'.$dirYName[0])){
            $expRegular=str_replace('%','[0-9A-Za-z]+',$dirYName[1]);
            $expRegular=str_replace('.','\.',$expRegular);
            $expRegular='/'.$expRegular.'/'; //crea la expresion regular necesaria para la busqueda
            for($j=0;$j<count($files);$j++){
                if(!is_dir($files[$j]) && preg_match($expRegular,$files[$j])==0){
                    $str=explode('/',$dirYName[0],2); //spq
                    $toret[]=$str[1].'/'.$files[$j];
                }
            }
        }
    }
    return $toret;
}

//caca
function comprobarTipoFile(){
    $k=0;
    if($files=@scandir('../CodigoAExaminar/Model')){
        for($i=0;$i<count($files);$i++){
            if(strpos($files[$i],'.php')!==false){
                $tipoCorrecto=false;
                $code=file('../CodigoAExaminar/Model/'.$files[$i],FILE_IGNORE_NEW_LINES);
                for($j=0;$j<count($code);$j++){
                    if(strpos($code[$j],'class ')!==false){
                        $tipoCorrecto=true;
                        break;
                    }
                    elseif(strpos($code[$j],'function ')!==false || strpos($code[$j],'switch ')!==false) break; //si se encuentra una de estas 2 antes es q no es una clase
                }
                if(!$tipoCorrecto){
                    $toret[$k][0]=$files[$i];
                    $toret[$k][1]='model';
                    $k++;
                }
            }
        }
    }
    if($files=@scandir('../CodigoAExaminar/View')){
        for($i=0;$i<count($files);$i++){
            if(strpos($files[$i],'.php')!==false){
                $tipoCorrecto=false;
                $code=file('../CodigoAExaminar/View/'.$files[$i],FILE_IGNORE_NEW_LINES);
                for($j=0;$j<count($code);$j++){
                    if(strpos($code[$j],'class ')!==false){
                        $tipoCorrecto=true;
                        break;
                    }
                    elseif(strpos($code[$j],'function ')!==false || strpos($code[$j],'switch ')!==false) break; //si se encuentra una de estas 2 antes es q no es una clase
                }
                if(!$tipoCorrecto){
                    $toret[$k][0]=$files[$i];
                    $toret[$k][1]='view';
                    $k++;
                }
            }
        }
    }
    if($files=@scandir('../CodigoAExaminar/Controller')){
        for($i=0;$i<count($files);$i++){
            if(strpos($files[$i],'.php')!==false){
                $tipoCorrecto=false;
                $code=file('../CodigoAExaminar/Controller/'.$files[$i],FILE_IGNORE_NEW_LINES);
                for($j=0;$j<count($code);$j++){
                    if(strpos($code[$j],'switch ')!==false){
                        $tipoCorrecto=true;
                        break;
                    }
                    elseif(strpos($code[$j],'function ')!==false || strpos($code[$j],'class ')!==false) break; //si se encuentra una de estas 2 antes es q no es un scripts
                }
                if(!$tipoCorrecto){
                    $toret[$k][0]=$files[$i];
                    $toret[$k][1]='controller';
                    $k++;
                }
            }
        }
    }
}

//comprueba la cabecera de los archivos de código
function comprobarCabeceras($dirOr){
    $dir='../CodigoAExaminar/'.$dirOr;
    $files=scandir($dir);
    for($i=0;$i<count($files);$i++){
        if(!is_dir($dir.'/'.$files[$i])){
            if(strpos($dir.'/'.$files[$i],'.php')!==false || strpos($dir.'/'.$files[$i],'.html')!==false || strpos($dir.'/'.$files[$i],'.css')!==false || strpos($dir.'/'.$files[$i],'.js')!==false || strpos($dir.'/'.$files[$i],'.c')!==false || strpos($dir.'/'.$files[$i],'.java')!==false || strpos($dir.'/'.$files[$i],'.sql')!==false || strpos($dir.'/'.$files[$i],'.‎py')!==false || strpos($dir.'/'.$files[$i],'.rb')!==false){
                $code=file($dir.'/'.$files[$i],FILE_IGNORE_NEW_LINES);
                $tieneAutor=false;
                $tieneFecha=false;
                $tieneFuncion=false;
                $esPrimer=true;
                $tipoCom='';
                for($j=0;$j<count($code);$j++){ //leemos el codigo
                    if($esPrimer){ //comprobamos el tipo de comentario q es usado
                        if(strpos($code[$j],'//')!==false){
                            $tipoCom='//';
                            $esPrimer=false;
                        }
                        elseif(strpos($code[$j],'/*')!==false){
                            $tipoCom='/*';
                            $esPrimer=false;
                        }
                        elseif(strpos($code[$j],'<!--')!==false){
                            $tipoCom='<!--';
                            $esPrimer=false;
                        }
                    }
                    if($tipoCom=='//' && strpos($code[$j],'//')===false) break; //cuando se acaba la cabecera dejamos de leer
                    if($tipoCom!='' && preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{2,4}/',$code[$j])==1) $tieneFecha=true;
                    if($tipoCom!='' && preg_match('/@?[Aa]uth?or/',$code[$j])==1) $tieneAutor=true;
                    elseif($tipoCom!='' && preg_match('/[0-9A-Za-z\s]+/',$code[$j])==1) $tieneFuncion=true; //entendemos cualquier frase en la cabecera como funcion
                    if(($tipoCom=='/*' && strpos($code[$j],'*/')!==false) || ($tipoCom=='<!--' && strpos($code[$j],'-->')!==false)) break; //cuando se acaba la cabecera dejamos de leer
                }
                
                if(!$tieneAutor || !$tieneFecha || !$tieneFuncion){
                    if(isset($toret)) $k=count($toret);
                    else $k=0;
                    $toret[$k][0]=$dirOr.'/'.$files[$i];
                    if(!$tieneAutor) $toret[$k][]='autor';
                    if(!$tieneFuncion) $toret[$k][]='funcion';
                    if(!$tieneFecha) $toret[$k][]='fecha';

                }
            }
        }
        elseif(!strpbrk($files[$i],'.')){
            $recur=comprobarCabeceras($dirOr.'/'.$files[$i]);
            if(!isset($toret)) $toret=array();
            $toret=array_merge($toret,$recur);
        }
    }
    return $toret;
}

//comprueba los comentarios de los archivos de código
function comprobarComentariosFuncion($dirOr){
    $dir='../CodigoAExaminar/'.$dirOr;
    $files=scandir($dir);
    for($i=0;$i<count($files);$i++){
        if(!is_dir($dir.'/'.$files[$i])){
            if(strpos($dir.'/'.$files[$i],'.php')!==false || strpos($dir.'/'.$files[$i],'.js')!==false || strpos($dir.'/'.$files[$i],'.c')!==false || strpos($dir.'/'.$files[$i],'.java')!==false || strpos($dir.'/'.$files[$i],'.‎py')!==false || strpos($dir.'/'.$files[$i],'.rb')!==false){
                if(isset($toret)) $k=count($toret);
                else $k=0;
                $code=file($dir.'/'.$files[$i],FILE_IGNORE_NEW_LINES);
                for($j=0;$j<count($code);$j++){ //leemos el codigo
                    if(preg_match('/^[A-Za-z][\w\s]+\((\$?[A-Za-z][\w\s]*,?)*\)\{/',$code[$j],$coincidencia)==1 && strpos($code[$j],'if')===false && strpos($code[$j],'for')===false && strpos($code[$j],'while')===false  && strpos($code[$j],'foreach')===false && strpos($code[$j],'switch')===false && strpos($code[$j],'//')===false && strpos($code[$j],'/*')===false && strpos($code[$j],'#')===false && preg_match('/^(\/\/)/',$code[$j-1])==0 && preg_match('/^(\/\*)/',$code[$j-1])==0 && preg_match('/^#/',$code[$j-1])==0){
                        $toret[$k][0]=$dirOr.'/'.$files[$i];
                        $toret[$k][]=$coincidencia;
                    }
                }
            }
        }
        elseif(!strpbrk($files[$i],'.')){
            $recur=comprobarComentariosFuncion($dirOr.'/'.$files[$i]);
            if(!isset($toret)) $toret=array();
            $toret=array_merge($toret,$recur);
        }
    }
    return $toret;
}

//comprueba q en la caepeta raiz solo se halle el index
function comprobarSoloIndex(){
    $toret=true;
    $files=scandir('../CodigoAExaminar');
    for($i=0;$i<count($files);$i++){
        if(!is_dir($files[$i]) && $files[$i]!='index.php'){
            $toret=false;
            break;
        }
    }
    return $toret;
}
?>