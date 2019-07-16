<?php



//namespace Modules\XRA\Helpers;

if (!\function_exists('ddd')) {
    function ddd($params)
    {
        /*
        try{
            \header('Content-type: text/html');
        }catch(\Exception $e)
                dd($e);
            // headers already sent
        }
        */
        $tmp = \debug_backtrace();
        $file = $tmp[0]['file'];
        $file = \str_replace('/', DIRECTORY_SEPARATOR, $file);
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        $doc_root = \str_replace('/', DIRECTORY_SEPARATOR, $doc_root);
        $dir_piece = \explode(DIRECTORY_SEPARATOR, __DIR__);
        $dir_piece = \array_slice($dir_piece, 0, -6);
        $dir_copy = \implode(DIRECTORY_SEPARATOR, $dir_piece);
        $file = \str_replace($dir_copy, $doc_root, $file);
        echo '<h3>LINE: ['.$tmp[0]['line'].']<br/>
		FILE: ['.$file.']<br/>
		</h3>';
        dd($params);
    }
}

if (!\function_exists('getFilename')) {
    function getFilename($params)
    {
        $tmp = \debug_backtrace();
        $class = class_basename($tmp[1]['class']);
        $func = $tmp[1]['function'];
        $params_list = collect($params)->except(['_token', '_method'])->implode('_');
        $filename = str_slug(
            \str_replace('Controller', '', $class).
                    '_'.\str_replace('do_', '', $func).
                    '_'.$params_list
                );

        return $filename;
    }
}

if (!\function_exists('setConfig')) {
    function setConfig($params)
    {
        $data = getConfig($params);
        $data = \array_merge($data, $params['data']);

        $config_files = getConfigFiles($params);
        if (\count($config_files) > 1) {
            foreach ($config_files as $k => $file) {
                arraySave(['filename' => $config_files[$k], 'data' => $data[$k]]);
            }
        } else {
            arraySave(['filename' => $config_files[0], 'data' => $data]);
        }
        //\Session::flash('status', $params['msg'].' '.\Carbon\Carbon::now());
        //return \Redirect::back();
    }
}

if (!\function_exists('getConfig')) {
    function getConfig($params)
    {
        $config_files = getConfigFiles($params);
        if (\count($config_files) > 1) {
            $data = [];
            foreach ($config_files as $k => $config_file) {
                $tmp = include $config_file;
                $data[$k] = $tmp;
            }
        } else {
            $data = include $config_files[0];
        }

        return $data;
    }
}

if (!\function_exists('getConfigFile')) {
    function getConfigFiles($params)
    {
        //if(count($params)>1){
        if (\is_dir(base_path('config/'.$params['file']))) {
            $tmps = (\array_keys(config($params['file'])));
            $files = [];
            foreach ($tmps as $tmp) {
                $files[$tmp] = base_path('config'.DIRECTORY_SEPARATOR.$params['file'].DIRECTORY_SEPARATOR.$tmp.'.php');
            }

            return $files;
        }
        //ddd($params);
        //}
        if (!isset($_SERVER['SERVER_NAME']) || '127.0.0.1' == $_SERVER['SERVER_NAME']) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }
        $server_name = str_slug(\str_replace('www.', '', $_SERVER['SERVER_NAME']));
        $config_file = base_path('config'.DIRECTORY_SEPARATOR.$server_name.DIRECTORY_SEPARATOR.$params['file']);
        if (!\file_exists($config_file)) {
            //ddd($config_file);
            if (\file_exists(base_path('config/'.$params['file']))) {
                //ddd(base_path('config/'.$params['file']));
                return [base_path('config/'.$params['file'])];
            }
            if (\file_exists(base_path('config/'.$params['file'].'.php'))) {
                //ddd('b');
                return [base_path('config/'.$params['file'].'.php')];
            }
            echo '<h3>'.$config_file.'</h3>';
            dd('<br/>LINE:['.__LINE__.']['.__FILE__.']');
        }
        return [$config_file];
    }
}

if (!\function_exists('arraySave')) {
    function arraySave($params)
    {
        \XRA\Extend\Services\ArrayService::save($params);
        /*
        \extract($params);
        $writer = new Zend\Config\Writer\PhpArray();
        $content = $writer->toString($data);
        $content = \str_replace('\\\\', '\\', $content);
        $content = \str_replace('\\\\', '\\', $content);
        //$content=str_replace("\\'","\'", $content);
        $content = \str_replace("'".storage_path(), 'storage_path()'.".'", $content);
        \File::put($filename, $content);
        */
    }
}

if (!\function_exists('in_admin')) {
    function in_admin()
    {
        return 'admin' == \Request::segment(1);
    }
}
if (!\function_exists('inAdmin')) {
    function inAdmin()
    {
        return 'admin' == \Request::segment(1);
    }
}


    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
if (!\function_exists('fullTextWildcards')) {
    /*protected */ function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if(strlen($word) >= 3) {
                $words[$key] = '+' . $word . '*';
            }
        }

        $searchTerm = implode( ' ', $words);

        return $searchTerm;
    }
}

if (!\function_exists('params2ContainerItem')) {
    function params2ContainerItem($params){
        $container=[];
        $item=[];
        foreach($params as $k=>$v){
            $pattern='/(container|item)([0-9]+)/';
            preg_match($pattern, $k,$matches);
            if(isset($matches[1]) && isset($matches[2]) ){
                $sk=$matches[1];
                $sv=$matches[2];
                $$sk[$sv]=$v;
            };
        }
        return [$container,$item];
    }
}


if (!\function_exists('getModuleModels')) {
    function getModuleModels($module){
        $mod=\Module::find($module);
        $mod_path=$mod->getPath().'\Models';
        $files = File::files($mod_path);
        $data=[]; 
        $ns='Modules\\'.$mod->name.'\\Models';  // con la barra davanti non va il search ?
        foreach($files as $file){
            $filename=$file->getRelativePathname();
            $ext='.php';
            if(ends_with($filename,$ext)){
                $tmp=new \stdClass();
                $name=substr(($filename),0,-strlen($ext));
                $tmp->class=$ns.'\\'.$name;
                $name=Str::snake($name);
                $tmp->name=$name;
                $data[$tmp->name]=$tmp->class;
                
            }
        }
        return $data;
    }
}

if (!\function_exists('money_format')) {
    // funzione copiata da https://php.net/manual/en/function.money-format.php
    // Improvement to Rafael M. Salvioni's solution for money_format on Windows: when no currency symbol is selected, in the formatting, the minus sign was also lost when the locale puts it in position 3 or 4. Changed $currency = '';  to: $currency = $cprefix .$csuffix;

    function money_format($format, $number) {
            $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
                    '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
            if (setlocale(LC_MONETARY, 0) == 'C') {
                setlocale(LC_MONETARY, '');
            }
            $locale = localeconv();
            preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
            foreach ($matches as $fmatch) {
                $value = floatval($number);
                $flags = array(
                    'fillchar' => preg_match('/\=(.)/', $fmatch[1], $match) ?
                            $match[1] : ' ',
                    'nogroup' => preg_match('/\^/', $fmatch[1]) > 0,
                    'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                            $match[0] : '+',
                    'nosimbol' => preg_match('/\!/', $fmatch[1]) > 0,
                    'isleft' => preg_match('/\-/', $fmatch[1]) > 0
                );
                $width = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
                $left = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
                $right = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
                $conversion = $fmatch[5];

                $positive = true;
                if ($value < 0) {
                    $positive = false;
                    $value *= -1;
                }
                $letter = $positive ? 'p' : 'n';

                $prefix = $suffix = $cprefix = $csuffix = $signal = '';

                $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
                switch (true) {
                    case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                        $prefix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                        $suffix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                        $cprefix = $signal;
                        break;
                    case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                        $csuffix = $signal;
                        break;
                    case $flags['usesignal'] == '(':
                    case $locale["{$letter}_sign_posn"] == 0:
                        $prefix = '(';
                        $suffix = ')';
                        break;
                }
                if (!$flags['nosimbol']) {
                    $currency = $cprefix .
                            ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                            $csuffix;
                } else {
                    $currency = $cprefix .$csuffix;
                }
                $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';

                $value = number_format($value, $right, $locale['mon_decimal_point'], $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
                $value = @explode($locale['mon_decimal_point'], $value);

                $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
                if ($left > 0 && $left > $n) {
                    $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
                }
                $value = implode($locale['mon_decimal_point'], $value);
                if ($locale["{$letter}_cs_precedes"]) {
                    $value = $prefix . $currency . $space . $value . $suffix;
                } else {
                    $value = $prefix . $value . $space . $currency . $suffix;
                }
                if ($width > 0) {
                    $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                                    STR_PAD_RIGHT : STR_PAD_LEFT);
                }

                $format = str_replace($fmatch[0], $value, $format);
            }
            return $format;
        }


}
