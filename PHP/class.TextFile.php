<?php 
/**
public showDayContent() 
오늘의 콘텐츠를 가져온다 만약 오늘의 콘텐츠가 없다면 새로 만듬

public saveContent($txt)
저장된 날짜에 콘텐츠 추가 및 수정

static fileCtrl($path,$mode,$args=null)
파일 읽기, 쓰기

static makeDir($date)
가져온 날짜에 파일 및 디렉토리 생성
**/
class TextFile
{
    # $param = array();
    # .today = date
    public function __construct($param=null)
    {
        $this->symbol = "###################"; # explode symbol
        
        $time = TIME();
        $date = date('Y',$time);
        $file = date('m',$time);
        
        $this->day = [];
        $this->day['today'] = date('Y-m-d',$time);
        $this->path = "{$_SERVER['DOCUMENT_ROOT']}/public/txt/{$date}/{$file}.txt";
        
        if (isset($param['today'])) {
            $this->day['today'] = $param['today'];
            $ym = explode('-',$param['today']);
            $this->path = "{$_SERVER['DOCUMENT_ROOT']}/public/txt/{$ym[0]}/{$ym[1]}.txt";
        } 
        
    }
    
    public function showDayContent()
    {
        if (!file_exists($this->path)) $this->path = self::makeDir($this->day['today']);
        $cont = self::fileCtrl($this->path,'r');
        $cont_by_day = explode($this->symbol,$cont);
        
        for ($i=1; $i<count($cont_by_day); $i+=2) {
            $date = trim($cont_by_day[$i]);
            if ($date == $this->day['today']) {
                $cont = $cont_by_day[$i+1];
                break;
            }
        }
        
        if ($i == count($cont_by_day)) {
            $cont = '';
            $title = PHP_EOL."{$this->symbol} {$this->day['today']} {$this->symbol}";
            self::fileCtrl($this->path,'a',array('txt'=>$title));
        }
        
        $rtn = [];
        $rtn['today'] = $this->day['today'];
        $rtn['content'] = $cont;
        return $rtn;
    }
    
    public function saveContent($txt)
    {
        if (!file_exists($this->path)) $this->path = self::makeDir($this->day['today']);   
        $cont = self::fileCtrl($this->path,'r');
        $cont_by_day = explode($this->symbol,$cont);
        $save_data = '';
        
        for ($i=1; $i<count($cont_by_day); $i++) {
            $content = trim($cont_by_day[$i]);
            if ($i%2 != 0)
                $save_data .= PHP_EOL.$this->symbol.' '.$content.' '.$this->symbol.PHP_EOL;
            else {
                $save_data .= (trim($cont_by_day[$i-1]) == $this->day['today']) ? trim($txt) : trim($content);
            }   
        }
        
        self::fileCtrl($this->path,'w',array('txt'=>$save_data,'today'=>$this->day['today']));
        
        return $save_data;
    }
    
    # $path = dir path
    # $mode = w, r, a
    # $args = array()
    #     .txt = text
    static function fileCtrl($path,$mode,$args=null)
    {
        if (!file_exists($path)) return false;   
        if (empty($path)) return false;
        if (empty($mode)) return false;
        
        $fp = fopen($path,$mode) or die("Unable to open file!");
        
        switch ($mode) {
            case 'r';
                $rtn = @fread($fp,filesize($path));
                break;
            case 'w':
                $rtn = fwrite($fp,$args['txt']);
                break;
            case 'a':
                $rtn = fwrite($fp,$args['txt']);
                break;
        }
        fclose($fp);
        return $rtn;
    }
    
    static function makeDir($date)
    {
        $year = date('Y',strtotime($date));
        $month = date('m',strtotime($date));
        
        $txt_dir = "{$_SERVER['DOCUMENT_ROOT']}/public/txt";
        $dir = $txt_dir."/".$year;
        if (!is_dir($dir)) @mkdir($dir,0755);
        
        $dir .= '/'.$month.'.txt';
        if (!file_exists($path)) @fopen($dir,'w');
        return $dir;
    }
}