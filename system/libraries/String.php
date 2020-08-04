<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        String 用來處理字串相關的功能類別。
  @version      1.0.0
  @date         2015-03-06
  @since        1.0.0 -> 新增此新類別。
**/

class String
{
    /**
    @brief      自动转换字符集 支持数组转换
    @param      String      $fContents
    @param      String      $from 编码
    @param      String      $to 转换后
    @return     String
    **/
    public function auto_charset( $fContents , $from , $to )
    {
        # ----------------------------------------------------------------------
        # 所有文字轉大寫
        # ----------------------------------------------------------------------
        $from = strtoupper( $from ) == ( 'UTF8' ) ? 'utf-8' : $from;
        # ----------------------------------------------------------------------
        # 所有文字轉大寫
        # ----------------------------------------------------------------------
        $to = strtoupper( $to ) == ( 'UTF8' ) ? 'utf-8' : $to;
        # ----------------------------------------------------------------------
        # 如果编码相同或者非字符串标量则不转换
        # ----------------------------------------------------------------------
        if ( strtoupper( $from ) === strtoupper( $to ) || empty( $fContents ) || ( is_scalar( $fContents ) && ! is_string( $fContents ) ) )
        {
            return $fContents;
        }
        # ----------------------------------------------------------------------
        # 如果傳入的型態是字串
        # ----------------------------------------------------------------------
        if ( is_string( $fContents ) )
        {
            # ------------------------------------------------------------------
            # 如果 mb_convert_encoding function有存在，就用這個轉換編碼
            # ------------------------------------------------------------------
            if ( function_exists( 'mb_convert_encoding' ) )
            {
                return mb_convert_encoding ( $fContents , $to , $from );
            }
            # ------------------------------------------------------------------
            # 如果 iconv function有存在，就用這個轉換編碼
            # ------------------------------------------------------------------
            elseif ( function_exists( 'iconv' ) )
            {
                return iconv( $from , $to , $fContents );
            }
            else
            {
                return $fContents;
            }
        }
        # ----------------------------------------------------------------------
        # 如果傳入的型態是陣列
        # ----------------------------------------------------------------------
        elseif ( is_array( $fContents ) )
        {
            foreach ( $fContents as $key => $val )
            {
                # --------------------------------------------------------------
                # 呼叫自己先轉換一次
                # --------------------------------------------------------------
                $_key = self::auto_charset( $key , $from , $to );
                # --------------------------------------------------------------
                # 呼叫自己先轉換一次，再存入陣列中
                # --------------------------------------------------------------
                $fContents[$_key] = self::auto_charset($val , $from , $to );
                # --------------------------------------------------------------
                # 如果原值與新值不符合則刪除舊值
                # --------------------------------------------------------------
                if ( $key != $_key ) unset( $fContents[$key] );
            }
            return $fContents;
        }
        else
        {
            return $fContents;
        }
    }

    /**
     @brief     检查字符串是否是UTF8编码
     @param     String      $string 字串
    **/
    public static function is_utf8( $string )
    {
        return preg_match('%^(?:
           [\x09\x0A\x0D\x20-\x7E]              # ASCII
           | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
           |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
           | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
           |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
           |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
           | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
           |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
       )*$%xs', $string );
    }

    /**
    @brief      字符串截取，支持中文和其他编码
    @param      String      $str 需要转换的字符串
    @param      String      $start 开始位置
    @param      String      $length 截取长度
    @param      String      $charset 编码格式
    @param      String      $suffix 截断显示字符,是否显示 '...'
    @return     String
    **/
    public function msubstr( $str , $start , $length , $charset = "UTF-8" , $suffix = false )
    {
        if ( function_exists( "mb_substr" ) )
        {
            $slice = mb_substr( $str , $start , $length , $charset );
        }
        elseif ( function_exists( 'iconv_substr' ) )
        {
            $slice = iconv_substr( $str , $start , $length , $charset );
        }
        else
        {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all( $re[$charset] , $str , $match );
            $slice = join( "" , array_slice( $match[0] , $start , $length ) );
        }
        if ( $suffix )
        {
            return $slice . "…";
        }
        return $slice;
    }
}