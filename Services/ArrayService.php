<?php
namespace Modules\Extend\Services;

use Cache;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;



class ArrayService
{
    public static $export_processor=1;
    /*
    
    */

    public static function toXLS($params){
        require_once(__DIR__.'/vendor/autoload.php');
        $data=$params['data'];
        $res=[];
        foreach ($data as $k => $v) {
            foreach($v as $k0 => $v0){
                if(!is_array($v0)){
                    $res[$k][$k0]=$v0;
                }
            }
        }
       $params['data']=$res;

        switch(self::$export_processor){
            case 1:return self::toXLS_phpoffice($params);break;
            case 2:return self::toXLS_Maatwebsite($params);break;
            case 3:return self::toXLS_phpexcel($params);break;
            default:
                ddd('unknown export_processor ['.$this->export_processor.']');
            break;
        }


    }

    public static function toXLS_phpoffice($params)
    {
        \extract($params);
        if(!is_array($data)){
            $data=[];
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $firstrow = collect($data)->first();
        if(!is_array($firstrow)){
            $firstrow=[];
        }
        $sheet->fromArray(\array_keys($firstrow), null, 'A1');
        $sheet->fromArray(
            $data,  	// The data to set
            null,        // Array values with this value will not be set
            'A2'         // Top left coordinate of the worksheet range where
                         //    we want to set these values (default is A1)
        );
        //$sheet->setCellValue('A1', 'Hello World !');
        $writer = new Xlsx($spreadsheet);
        $pathToFile = 'c:\\download\\xls\\'.$filename.'.xlsx';
        $writer->save($pathToFile); //$writer->save('php://output'); // per out diretto ?
        if (!isset($out)) {
            return response()->download($pathToFile);
        }
        if(!isset($text)){
            $text='text';
        }
        switch ($out) {
            case 'link': return view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'xls')->with('text', $text);
            case 'download': response()->download($pathToFile);
            // no break
            case 'file':  return $pathToFile;
            case 'link_file':
                $link = view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'xls')->with('text', $text);

                return [$link, $pathToFile];
        }
    }

    /*
    // redirect output to client browser
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="myfile.xlsx"');
    header('Cache-Control: max-age=0');
    */

    public static function toXLS_Maatwebsite($params)
    {
        \extract($params);
        Excel::create($filename, function ($excel) use ($data) {
            $excel->sheet('Excel sheet', function ($sheet) use ($data) {
                $sheet->setOrientation('landscape');
                $sheet->freezeFirstRow();
                $sheet->fromArray($data); //anche fromModel ..
            });
        })->export('xlsx');
    }

    public static function toXLS_phpexcel($params)
    {
        \Debugbar::disable();
        $objPHPExcel = new \PHPExcel();
        $text = '';
        \extract($params);
        $data = \array_values($data);
        if (0 == \count($data)) {
            echo '<h3>nulla dentro DATA</h3>';

            return 'nulla dentro DATA';
        }
        \reset($data);
        $text .= 'N Righe: '.\number_format(\count($data), 0, ',', "'");

        $ews = $objPHPExcel->getSheet(0);
        $ews->setTitle('Data');
        $collection = collect($data);
        $firstrow = $collection->first();

        $ews->fromArray(\array_keys($firstrow), ' ', 'A1');
        $ews->fromArray($data, ' ', 'A2');
        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); //'Excel5' , 'Excel2007'
        $writer->setIncludeCharts(true);
        //$writer->save('php://output');
        $pathToFile = 'c:\\download\\xls\\'.$filename.'.xlsx';
        $res = $writer->save($pathToFile);
        //ddd(filesize($pathToFile));
        $size = \filesize($pathToFile);
        if ($size > 1024 * 1024) {
            $text .= \chr(13).'<br/>Size: '.\round($size / (1024 * 1024), 2).' MB';
        } else {
            $text .= \chr(13).'<br/>Size: '.\round($size / (1024), 2).' KB';
        }
        //return 'aaaa';
        $caption = '';
        $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
        //FILE_OP::showDownloadLink($pathToFile, $caption);
        if (!isset($out)) {
            return response()->download($pathToFile);
        }
        if(!isset($text)){
            $text='testo ';
        }
        switch ($out) {
            case 'link': return view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'xls')->with('text', $text);
            case 'download': response()->download($pathToFile);
            // no break
            case 'file':  return $pathToFile;
            case 'link_file':
                $link = view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'xls')->with('text', $text);

                return [$link, $pathToFile];
        }
    }

    public static function toXLS_pear($params)
    {
        //echo '<pre>';print_r($params); echo '</pre>';
        $caption = '';
        \extract($params);
        if (!isset($nomefile) && isset($filename)) {
            $nomefile = $filename;
        }
        \ini_set('max_execution_time', 3600);
        //require_once 'Spreadsheet/Excel/Writer.php';
        //global $registry;
        //$download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $download_dir = 'c:\download';
        $datepub = \date('Ymd');
        $backupdir = $download_dir.'/excel/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);
        $filename = $backupdir.$nomefile.'-'.$datepub.'.xls';
        $workbook = new \Spreadsheet_Excel_Writer($filename);

        $worksheet = $workbook->addWorksheet('My first worksheet');
        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_title = $workbook->addFormat();
        $format_title->setBold();
        $format_title->setColor('yellow');
        $format_title->setPattern(1);
        $format_title->setFgColor('blue');
        // let's merge
        $format_title->setAlign('merge');

        $format_2 = $workbook->addFormat(['Size' => 11, 'Align' => 'center', 'vAlign' => 'vjustify', //'vAlign' => 'vcenter',
            'Color' => 'black', 'Bold' => 1, 'Pattern' => 1, 'FgColor' => 'orange', ]);

        $data = \array_values($data);
        \reset($data);
        // echo '<pre>[';print_r($data); echo ']</pre>';
        $i = 0;
        $head = [];
        foreach ($data as $k => $v) {
            // echo '<br/>['.$k.']<pre>';print_r($v);'</pre>';
            $my = [];
            \reset($v);
            foreach ($v as $k1 => $v1) {
                $v1 = \str_replace(',', '.', $v1);

                $my[$k1] = $v1;
            }
            if (0 == $i) {
                //$head = array_keys($my);
                if (!isset($fieldlabels)) {
                    //self::print_x($v);
                    if (\is_object($v)) {
                        $fieldlabels = \array_keys(\get_object_vars($v));
                    } else {
                        $fieldlabels = \array_keys($v);
                    }
                    //$fieldlabels=array_keys($v);
                    //ddd('qui');
                }
                $head = \array_values($fieldlabels);
                //echo '<pre>';print_r($head); echo '</pre>';
                foreach ($head as $k3 => $v3) {
                    $head[$k3] = \str_replace('_', ' ', $v3);
                }
                $worksheet->writeRow($i, 0, $head, $format_2);
                //$i++;
            }
            $worksheet->writeRow($k + 1, 0, $my);
            //$i++;
        }
        //echo '<h3>HEAD</h3><pre>';print_r($head);echo '</pre>';

        $workbook->close();
        // echo '<div style="border:1px solid blue;margin:10px;padding:10px;width:550px;">';
        //die(dirname($filename));
        // mkdir(dirname($filename), 0, true);
        FILE_OP::recursive_mkdir(\dirname($filename));
        if (\file_exists($filename)) {
            $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
            FILE_OP::showDownloadLink($filename, $caption);
            echo '<br style="clear:both"/>';
        /*
        echo '<br><a href="' . str_replace($_SERVER["DOCUMENT_ROOT"], 'http://' . $_SERVER["HTTP_HOST"], $filename) . '">' . str_replace($backupdir . "/", "", $filename) . '</a>';
        //echo '&nbsp;&nbsp;<a href="?action=cancellafile&filename=">cancella</a>'
        echo ': ' . filesize($filename) / 1024 . ' Kbytes';
        */
        } else {
            echo '<br>File NON Creato '.$filename.'] '; //< br > [" . $cmd . "] < br > [" . $retval . "]";
        }
        //  echo '</div>';
    }

    //end function

    public static function toCSV($params)
    {
        //return self::toCSV_php($params);
        //return self::toCSV_phpexcel($params);
        require_once(__DIR__.'/vendor/autoload.php');
        return self::toCSV_phpoffice($params);
    }

    public static function toCSV_phpoffice($params)
    {
        \extract($params);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $firstrow = collect($data)->first();
        $sheet->fromArray(\array_keys($firstrow), null, 'A1');
        $sheet->fromArray(
            $data,      // The data to set
            null,        // Array values with this value will not be set
            'A2'         // Top left coordinate of the worksheet range where
                         //    we want to set these values (default is A1)
        );
        //$sheet->setCellValue('A1', 'Hello World !');
        $writer = new Csv($spreadsheet);
        $writer->setEnclosure('"'); //  Set enclosure to "
        $writer->setDelimiter(';'); //  Set delimiter to a semi-colon
        $writer->setLineEnding("\r\n");

        $pathToFile = 'c:\\download\\xls\\'.$filename.'.csv';
        $writer->save($pathToFile); //$writer->save('php://output'); // per out diretto ?
        if (!isset($out)) {
            return response()->download($pathToFile);
        }
        switch ($out) {
            case 'link': return view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'csv')->with('text', $text);
            case 'download': response()->download($pathToFile);
            // no break
            case 'file':  return $pathToFile;
            case 'link_file':
                $link = view('extend::download_icon')->with('file', $pathToFile)->with('ext', 'csv')->with('text', $text);

                return [$link, $pathToFile];
        }
    }
    /* -- obsolete 
    public static function toCSV_phpexcel($params)
    {
        \Debugbar::disable();
        $objPHPExcel = new \PHPExcel();
        \extract($params);
        $data = \array_values($data);
        if (0 == \count($data)) {
            echo '<h3>nulla dentro DATA</h3>';

            return 'nulla dentro DATA';
        }
        \reset($data);
        $ews = $objPHPExcel->getSheet(0);
        $ews->setTitle('Data');
        $collection = collect($data);
        $firstrow = $collection->first();

        //$ews->fromArray(array_keys($firstrow), ' ', 'A1');
        $ews->fromArray($data, ' ', 'A1');

        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV'); //'Excel5' , 'Excel2007','CSV'
        $writer->setDelimiter(';');
        //$writer->setIncludeCharts(true);
        //$writer->save('php://output');
        $pathToFile = 'c:\\download\\csv\\'.$filename.'.csv';
        $writer->save($pathToFile);
        //return 'aaaa';
        $caption = '';
        $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
        //FILE_OP::showDownloadLink($pathToFile, $caption);
        return response()->download($pathToFile);
    }
    */

    public static function toCSV_php($params)
    {
        global $registry;
        $caption = '';
        $delimiter = ';';
        \extract($params);
        if (0 == \count($data)) {
            return null;
        }
        //ob_start();
        //$download_dir = $registry->conf->settings['root']['conf']['xot']['download'];
        $download_dir = 'c:\download';
        $datepub = \date('Ymd');
        $backupdir = $download_dir.'/csv/';
        $backupdir = \str_replace('\\', '/', $backupdir);
        $backupdir = \str_replace('//', '/', $backupdir);
        $filename = $backupdir.$filename.'-'.$datepub.'.csv';

        $df = \fopen($filename, 'w');
        \reset($data);
        //fputcsv($df, array_keys());
        foreach ($data as $row) {
            if (\is_object($row)) {
                $row = \get_object_vars($row);
            }
            \fputcsv($df, $row, $delimiter);
        }
        \fclose($df);
        //return ob_get_clean();
        FILE_OP::recursive_mkdir(\dirname($filename));
        if (\file_exists($filename)) {
            $caption .= '<br/>N Righe = <b>'.\count($data).'</b>';
            FILE_OP::showDownloadLink($filename, $caption);
            echo '<br style="clear:both"/>';
        } else {
            echo '<br>File NON Creato '.$filename.'] '; //< br > [" . $cmd . "] < br > [" . $retval . "]";
        }
    }

    public static function toCSV_Maatwebsite($params)
    {
        \Config::set('excel::csv.delimiter', ';');
        \Config::set('excel.csv.delimiter', ';');
        \Maatwebsite\Excel\Facades\Excel::setDelimiter(';');

        return \Maatwebsite\Excel\Facades\Excel::create($filename, function ($excel) use ($data) {
            $excel->sheet('mySheet', function ($sheet) use ($data) {
                //$sheet->setDelimiter(';');
                //$sheet->row(1, function($row) {
                //    $row->setBackground('#000000');
                // call cell manipulation methods
                //$sheet->fromArray($data, null, 'A1', false, false);
                $sheet->fromArray($data, null, 'A1', false, true);
            });
        })->download('csv');
    }


    public static function diff_assoc_recursive($arr_1,$arr_2){
        /*
        $ris=$arr_1;
        foreach($arr_2 as $v){
            if(in_array($v,$ris)){
                echo '<pre>';print_r($v);echo '</pre>';
                ddd($ris);
            }
        }
        ddd($ris);
        */
        $coll_1=collect($arr_1);
        $coll_2=collect($arr_2);
        $ris=$coll_1->filter(function($value,$key) use($arr_2,$coll_2) {
            return !in_array($value,$arr_2);
        });
        return $ris->all();


    }

    public static function rangeIntersect($a0, $b0, $a1, $b1)
    {
        if ($a1 >= $a0 && $a1 <= $b0 && $b0 <= $b1) {
            return [$a1, $b0];
        }
        if ($a0 >= $a1 && $a0 <= $b0 && $b0 <= $b1) {
            return [$a0, $b0];
        }
        if ($a1 >= $a0 && $a1 <= $b1 && $b1 <= $b0) {
            return [$a1, $b1];
        }
        if ($a0 >= $a1 && $a0 <= $b1 && $b1 <= $b0) {
            return [$a0, $b1];
        }

        //return [0, 0];
        return false;
    }

    public static function raggruppa($params)
    {
        if (!isset($params['key_array']) && isset($params['key'])) {
            $params['key_array'] = $params['key'];
        }
        \extract($params);
        if (!\is_array($data)) {
            return [];
        }
        //*-- made with collection
        $collection = collect($data);
        $grouped = $collection->groupBy(function ($item) use ($key_array) {
            $key_tmp = [];
            foreach ($key_array as $k) {
                $key_tmp[] = $item[$k];
            }

            return \implode('-', $key_tmp);
        });

        return $grouped->all();
        //*/

        \reset($data);
        $ris = [];
        //foreach($data as $k => $v) {
        foreach ($data as $k => $v) {
            if (!\is_array($key_array)) {
                ddd($key_array);
            }
            \reset($key_array);
            $skey = [];
            //while (list($k1, $v1) = each($key_array)) {
            foreach ($key_array as $k1 => $v1) {
                if (\is_object($v)) {
                    if (!isset($v->$v1)) {
                        //self::print_x($v1);
                        //self::print_x($v);
                        //ddd('qui');
                        $v->$v1 = '';
                    }
                    $v2 = $v->$v1;
                } else {
                    if (isset($v[$v1])) {
                        $v2 = $v[$v1];
                    } else {
                        $v2 = '';
                    }
                }
                $skey[] = $v2;
            }
            $skey = \implode('-', $skey);
            if (!isset($ris[$skey])) {
                $ris[$skey] = [];
            }
            $ris[$skey][] = $v;
        }

        return $ris;
    }

    public static function subtotale($params)
    {
        $fields = [];
        \extract($params);
        $ris = [];
        $fields = \array_merge($fields, $key);
        $fields = \array_merge($fields, $add); //in piu forse
        $fields = \array_unique($fields);
        if (!\is_array($data)) {
            return $ris;
        }

        //--------------
        //*
        $collection = collect($data);
        $grouped = $collection->groupBy(function ($item) use ($key,$add,$fields) {
            $key_tmp = [];
            foreach ($key as $k) {
                $key_tmp[] = $item[$k];
            }

            return \implode('-', $key_tmp);
        })->map(function ($item) use ($add,$fields) {
            foreach ($fields as $i) {
                /*
                if(!isset($item->first()[$i])){
                    echo '<h3>Not set ['.$i.']</h3>';
                    ddd($item->first());
                }
                */
                $item->$i = $item->first()[$i];
            }
            foreach ($add as $i) {
                $item->$i = $item->sum($i);
            }

            return $item;
        });

        return $grouped->all();
        //*/
        //--------------
        \reset($data);

        foreach ($data as $k => $v) {
            if (\is_array($v)) {
                $v = self::toObject($v);
            }
            $skey = [];
            \reset($key);
            foreach ($key as $kk => $vk) {
                if (!isset($v->$vk)) {
                    echo '<pre>[v]';
                    \print_r($v);
                    echo '[/v]</pre>';
                    echo '<pre>[vk]';
                    \print_r($vk);
                    echo '[/vk]</pre>';
                    ddd('qui');
                }
                $skey[] = $v->$vk;
            }
            $skey = \implode('-', $skey);
            if (!isset($ris[$skey])) {
                $obj = new \stdclass();
                \reset($fields);
                foreach ($fields as $kf => $vf) {
                    if (!isset($v->$vf)) {
                        $v->$vf = 0;
                    }
                    $obj->$vf = $v->$vf;
                }
                $ris[$skey] = $obj;
            } else {
                \reset($add);
                foreach ($add as $ka => $va) {
                    if (!isset($v->$va)) {
                        $v->$va = 0;
                    }
                    if (!isset($ris[$skey]->$va)) {
                        $ris[$skey]->$va = 0;
                    }
                    $ris[$skey]->$va += $v->$va;
                    //echo '<pre>';print_r($ris[$skey]);echo '</pre>';
                    //echo '<pre>';print_r($v);echo '</pre>';
                    //echo '<pre>';print_r($va);echo '</pre>';
                    //ddd('qui');
                }
            }
        }

        return $ris;
    }

    public static function save($params){
        //require_once(__DIR__.'/vendor/autoload.php');
        \extract($params);
        /*
        $writer = new \Zend\Config\Writer\PhpArray();
        $writer->setUseBracketArraySyntax(true);
        $content = $writer->toString($data);
        $content = \str_replace('\\\\', '\\', $content);
        $content = \str_replace('\\\\', '\\', $content);
        //$content=str_replace("\\'","\'", $content);
        $content = \str_replace("'".storage_path(), 'storage_path()'.".'", $content);
        */
        
        $content='<'.'?php '.chr(13).'return '.var_export($data,true).';'.chr(13);
        $content=str_replace('stdClass::__set_state','(object)',$content);
         \File::makeDirectory(\dirname($filename), 0775, true, true);
        \File::put($filename, $content);
    }

}//end class
