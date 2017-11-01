<?php
namespace lib;
class Func
{

    public static function getObject($class, $class_args = null) {
        $reflection = new ReflectionClass($class);
        if ($class_args) {
            return $reflection->newInstanceArgs($class_args);
        } else {
            return $reflection->newInstance();
        }
    }

    public static function hyphen2camel($string, $upfirst = false) {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        if (!$upfirst) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    public static function isUtf8($string) {
        return preg_match ( '%^(?:
				[\x09\x0A\x0D\x20-\x7E]            # ASCII
				| [\xC2-\xDF][\x80-\xBF]                # non-overlong 2-byte
				|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}    # straight 3-byte
				|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
				|  \xF0[\x90-\xBF][\x80-\xBF]{2}        # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3}            # planes 4-15
				|  \xF4[\x80-\x8F][\x80-\xBF]{2}        # plane 16
		)*$%xs', $string );
    }

    public static function isValidPhone($phone)  {
        return ($phone && preg_match('/^1[34578]{1}\d{9}$/', $phone));
    }

    public static function isValidEmail($email, $check_dns = false) {
        $isValid = true;
        $atIndex = strrpos ( $email, '@' );
        if (is_bool ( $atIndex ) && ! $atIndex) {
            $isValid = false;
        } else {
            $domain = substr ( $email, $atIndex + 1 );
            $local = substr ( $email, 0, $atIndex );
            $localLen = strlen ( $local );
            $domainLen = strlen ( $domain );
            if ($localLen < 1 || $localLen > 64) {
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                $isValid = false;
            } else if ($local [0] == '.' || $local [$localLen - 1] == '.') {
                $isValid = false;
            } else if (preg_match ( '/\\.\\./', $local )) {
                $isValid = false;
            } else if (! preg_match ( '/^[A-Za-z0-9\\-\\.]+$/', $domain )) {
                $isValid = false;
            } else if (preg_match ( '/\\.\\./', $domain )) {
                $isValid = false;
            } else if (! preg_match ( '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace ( "\\\\", "", $local ) )) {
                if (! preg_match ( '/^"(\\\\"|[^"])+"$/', str_replace ( "\\\\", "", $local ) )) {
                    $isValid = false;
                }
            }
            if ($isValid && $check_dns) {
                if (!(checkdnsrr($domain,'MX') || checkdnsrr($domain,'A'))) {
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }

    /**
     * 发送邮件
     */
    public static function mail($mailcfg, $to, $title, $content, $cc = null) {
        $mail = new \PHPMailer();
        $mail->isSMTP();
        foreach ($mailcfg as $prop=>$value) {
            $mail->$prop = $value;
        }

        $mail->setFrom($mailcfg['Username'], '小程序');
        $mail->addAddress($to);

        $mail->WordWrap = 50;
        $mail->isHTML(true);

        $mail->Subject = $title;
        $mail->Body    = $content;
        $mail->AltBody = strip_tags($content);

        if(!$mail->send()) {
            return result(-1, '发送邮件失败，原因：'.$mail->ErrorInfo);
        }

        return result(1, '发送邮件成功');
    }
    
    public static function randomString($length = 8, $number_only=false) {
        $result = '';
        $max = $number_only ? 0 : 2;
        for($i = 0; $i < $length; $i ++) {
            switch (rand ( 0, $max )) {
                case 0 : {
                    $result .= rand ( 0, 9 );
                    break;
                }
                case 1 : {
                    $result .= chr ( rand ( 65, 90 ) );
                    break;
                }
                default : {
                    $result .= chr ( rand ( 97, 122 ) );
                }
            }
        }
        return $result;
    }

    public static function randomLuckyNum($min, $max) {
        do {
            $rand = rand($min, $max);
        } while (strpos($rand, '4') !== false);
        return $rand;
    }

    public static function cstrlen($str, $encoding = 'utf-8') {
        return mb_strlen($str, $encoding);
    }


    private static function urlencodeArray(&$array) {
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $array[$key] = self::urlencodeArray($value);
            } else {
                $array[$key] = urlencode($value);
            }
        }
    }

    public static function cjsonEncode($data) {
        if (! empty($data)) {
            // 保护中文，微信api不支持中文转义的json结构
            $data = urldecode(json_encode(self::urlencodeArray($data)));
        }
        return $data;
    }

    public static function filterVal($val, $html_filter_func='htmlspecialchars') {
        $rs = null;
        if (is_array($val)) {
            foreach ($val as $key=>$value) {
                $rs[$key] = self::filterVal($value, $html_filter_func);
            }
        } else {
            if (!get_magic_quotes_gpc()) {
                $val = addslashes($val);
            }
            $rs = $html_filter_func ? $html_filter_func($val) : $val;

        }
        return $rs;
    }

    public static function unfilterVal($val, $html_filter_func='htmlspecialchars') {
        $rs = $val;
        if (is_array($val)) {
            foreach ($val as $key=>$value) {
                $rs[$key] = self::unfilterVal($value, $html_filter_func);
            }
        } else {
            $decode_func = $html_filter_func.'_decode';
            $rs = stripslashes($decode_func($val));
        }
        return $rs;
    }

    public static function purifyHtml($val) {
        require_once VV_FILE_PATH_SRC.'lib/plugin/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php';

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,a[target|href],img[src]');
        $config->set('Attr.AllowedClasses', array());
        //$config->set('CSS.AllowedProperties', 'text-align');
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($val);
    }

    public static function setDotKeyValue($key, $val, &$ar) {
        $keys = explode('.', $key);
        $count = count($keys);
        if ($count == 1) {
            $ar[$key] = $val;
        } else  {
            $rs = &$ar;
            for ($i = 0; $i < $count - 1; $i++) {
                $k = $keys[$i];
                if (isset($rs[$k])) {
                    $rs = &$rs[$k];
                } else {
                    $rs[$k] = null;
                    $rs = &$rs[$k];
                }
            }
            $rs[$keys[$count - 1]] = $val;
        }
    }

    public static function getDotKeyValue($key, $ar, $def = null) {
        if (!$ar) return $def;

        $keys = explode('.', $key);
        $rs = $ar;
        foreach ($keys as $k) {
            if (!isset($rs[$k])) {
                return $def;
            }
            $rs = $rs[$k];
        }
        return $rs;
    }

    public static function removeDotKeyValue($key, &$ar) {
        $keys = explode('.', $key);
        $count = count($keys);
        if ($count == 1) {
            unset($ar[$key]);
        } else  {
            $rs = &$ar;
            for ($i = 0; $i < $count - 1; $i++) {
                $k = $keys[$i];
                if (isset($rs[$k])) {
                    $rs = &$rs[$k];
                } else {
                    unset($rs);
                    return;
                }
            }
            unset($rs[$keys[$count - 1]]);
        }
    }

    public static function extractFileDir($strFileName) {
        if (! file_exists ( $strFileName )) {
            $strDir = dirname ( $strFileName );
            $strDir = str_replace ( '\\', '/', $strDir );
        } else {
            $strDir = dirname ( $strFileName );
            $strDir = str_replace ( '\\', '/', realpath ( $strDir ) );
        }
        return $strDir;
    }

    public static function extractFilePath($strFileName) {
        $strPath = self::extractFileDir ( $strFileName ) . '/';
        return $strPath;
    }

    public static function importFile($strFileName) {
        if (is_string($strFileName)) {
            require_once ($strFileName);
        } else {
            vvphp_ErrorHandler::exception(new Exception('未能加载指定的文件', -1));
        }
    }

    public static function url($dis = null, $params = array(), $options = array()) {
        if (VVPHP::$Server->getSysParam('make_abs_url') || $options['make_abs_url']) {
            $options['make_abs_url'] = true;
            return self::urlFor(null, $dis, $params, $options);
        } else {
            $str = ($params) ? http_build_query($params, '', '&') : '';
            $dis = ($dis) ? 'dis='.$dis : '';
            $url = ($str) ? $dis.'&'.$str : $dis;
            return '?'.$url;
        }
    }

    public static function urlFor($app = null, $dis = null, $params = array(), $options = array()) {
        if (!$app) {
            $app = VVPHP::$Request->AppName;
        }
        if ($options && array_key_exists('make_abs_url', $options)) {
            if ($options['abs_url_path']) {
                $url = $options['abs_url_path'];
            } else {
                $url = VVPHP::$Server->getAppParam($app, 'absolute_url_path');
                if (!$url) {
                    $url = 'http://'.VVPHP::$SiteParams['vvphp_site_domain'].VVPHP::$Server->getAppParam($app, 'url_path');
                }
            }
        } else {
            $url = VVPHP::$Server->getAppParam($app, 'url_path');
        }
        $str = ($params) ? http_build_query($params, '', '&') : '';
        $dis = ($dis) ? 'dis='.$dis : '';
        $str = ($str) ? $dis.'&'.$str : $dis;
        return $str ? $url.'?'.$str : $url;
    }

    public static function getParamsFromUrl($url, $param_name = null) {
        $var = parse_url($url, PHP_URL_QUERY);
        $var = html_entity_decode($var);
        $var = explode('&', $var);
        $arr = array();
        if ($var) {
            foreach ($var as $val) {
                $x = explode('=', $val);
                if (!isset($x[1])) {
                    continue;
                }
                $arr[$x[0]] = $x[1];
            }
            unset($val, $x, $var);
        }
        if ($param_name) {
            return $arr[$param_name];
        }
        return $arr;
    }


    public static function arrayValuesBy($needle_keys, $search_array) {
        if (is_string($needle_keys)) {
            $needle_keys = explode(',', $needle_keys);
        }
        return array_intersect_key($search_array, array_fill_keys($needle_keys, 1));
    }

    /**
     * 从records里面取出column数组的值
     * @param $fields
     * @param $rows
     * @param bool|true $unique
     * @return array
     */
    public static function getFieldDataFrom($fields, $rows, $unique = true) {
        if (!$rows) {
            $rows = array();
        }
        $ar_fields = explode(',', $fields);
        $result = array();
        foreach ($rows as $row) {
            foreach ($ar_fields as $field) {
                $result[] = $row[$field];
            }
        }
        return $unique ? array_unique($result) : $result;
    }

    public static function indexByField($field, $rows, $multi = false) {
        if (!$rows) {
            $rows = array();
        }
        if ($multi) {
            foreach ($rows as $row) {
                $result[$row[$field]][] = $row;
            }
        } else {
            foreach ($rows as $row) {
                $result[$row[$field]] = $row;
            }
        }
        return $result;
    }

    public static function toBizResult($code, $msg, $data=null) {
        return array('code'=>$code, 'msg'=>$msg, 'data'=>$data);
    }

    public static function toAmountFormat($amt) {
        return sprintf('%.2f', round($amt * 100) / 100 );
    }

    public static function rangeDate($from, $to, $options = array()) {
        $rs = array();
        $cur = strtotime($from);
        $end = strtotime($to);
        while ($cur < $end) {
            $rs[] = date('Y-m-d', $cur);
            $cur = strtotime('+1 day', $cur);
        }
        $rs[] = date('Y-m-d', $cur);
        return $rs;
    }


    public static function getExcelCol($col) {
        $str     = "ZABCDEFGHIJKLMNOPQRSTUVWXY";
        $col_str = "";
        do
        {
            $col_tmp  = $col % 26;
            $col      = $col_tmp == 0 ? intval($col / 26) - 1 : intval($col / 26);
            $col_str  = $str[$col_tmp].$col_str;
        }while( $col );

        return $col_str;
    }

    public static function rangeExcelCol($col) {
        $rs = array();
        for ($i = 0; $i < $col; $i++) {
            $rs[] = self::getExcelCol($i + 1);
        }
        return $rs;
    }

    public static function datetimeDiff($time1, $time2 = null, $format = '%r%a 天') {
        if (!$time1) {
            return '无';
        }
        $date1 = date_create($time1);
        $date2 = $time2 ? date_create($time2) : date_create();
        $diff = date_diff($date1, $date2);
        return $diff->format($format);
    }

    public static function post($url, $params) {
        if (!is_string($params)) {
            $params = http_build_query($params, '', '&');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    public static function curl_get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 二维数组按某值排序
     */
    public static function arrayMulisort($rows, $field, $sort=SORT_DESC, $field_second=false, $sort_second=SORT_DESC, $field_third=false, $third_sort=SORT_DESC) {
        $arr = $else = $third = array();
        foreach ($rows as $rv) {
            $arr[] = $rv[$field];
            if (!empty($field_second)) {
                $else[] = $rv[$field_second];
            }
            if (!empty($field_third)) {
                $third[] = $rv[$field_third];
            }
        }
        if (!empty($third) && !empty($else)) {
            array_multisort($arr, $sort, $else, $sort_second, $third, $third_sort, $rows);
        } elseif (!empty($else)) {
            array_multisort($arr, $sort, $else, $sort_second, $rows);
        } else {
            array_multisort($arr, $sort, $rows);
        }
        return $rows;
    }

    /**
     * @name php获取中文字符拼音首字母
     * @param $str
     * @return null|string
     */
    public static function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }
    /**
     * 无限极分类
     */
    public static function tree($arr, $parent_id, $filed = 'parent')
    {
        $tree = array();
        foreach($arr as $a){
            if($a[$filed] == $parent_id){
                $tree[] = $a;
            }
            foreach($arr as &$t){
                $d = self::tree($arr, $t['id'], $filed);
                $t['sub'] = $d;
            }
        }
        return $tree;
    }
}