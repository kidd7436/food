<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        日期时间操作類，用來處理日期、時間資料。
  @version      1.0.1
  @date         2015-02-27
  @since        1.0.1 -> 將日期輔助中的部分函式合併至此。
  @since        1.0.0 -> 新增此新類別。
  @pre          請注意時區是否正常。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class Date
{
    /**
    @brief      傳入一個時間戳記，會自動和當下時間交互判斷是多久以前的資料。
    @param      Int         $oldTime
    @retval     String
    @remarks    此範例返回：22分钟前。
    @code{.unparsed}
    $this->Core_Date->xTimeAgo( 122121230 );
    @endcode
    **/
    public function xTimeAgo ( $oldTime )
    {
        # ----------------------------------------------------------------------
        # 先用當前時間減掉傳入的時間
        # ----------------------------------------------------------------------
        $t = time() - $oldTime;
        # ----------------------------------------------------------------------
        # 初始化時間區間陣列
        # ----------------------------------------------------------------------
        $f = array
        (
            '31536000'  => '年',
            '2592000'   => '个月',
            '604800'    => '星期',
            '86400'     => '天',
            '3600'      => '小时',
            '60'        => '分钟',
            '1'         => '秒'
        );
        # ----------------------------------------------------------------------
        # 跑迴圈進行逐筆資料比對
        # ----------------------------------------------------------------------
        foreach ( $f as $k => $v )
        {
            if ( ( $c = floor( $t / (int)$k ) ) != 0 )
            {
                return $c . $v . '前';
            }
        }
    }

    /**
    @brief      人性化地显示时间，新浪微博的实现
    @param      int|string    $time 自动判断是Unix时间戳，还是一个格式化后的时间字符串
    @retval     string
    @remarks    此範例返回："22分钟前"
    @code{.unparsed}
    $this->Core_Date->showtime( 122121230 );
    @endcode
    @attention  若日期大於2天，則返回 2015-02-26 15:11 ( Y-m-d H:i )
    **/
    public function showtime( $time )
    {
        # ----------------------------------------------------------------------
        # 如果传入的时间不是数字型态就先转换为( 时间戳记 )
        # ----------------------------------------------------------------------
        if ( ! is_numeric( $time ) ) $time = strtotime( $time );
        # ----------------------------------------------------------------------
        # 用当前时间扣除传入的时间
        # ----------------------------------------------------------------------
        $timej = time() - $time;
        # ----------------------------------------------------------------------
        # 如果是 ( 86400 * 2 ) 两天内的资料再另外处理显示的方式
        # ----------------------------------------------------------------------
        if ( $timej < ( 86400 * 2 ) )
        {
            # ------------------------------------------------------------------
            # 如果时间大于 86400( 一天 )
            # ------------------------------------------------------------------
            if ( $timej > 86400 )
            {
                return '昨天';
            }
            # ------------------------------------------------------------------
            # 如果大于 3600( 一小时 )
            # ------------------------------------------------------------------
            else if ( $timej > 3600 )
            {
                return floor( $timej / 3600 ) . "小时前";
            }
            # ------------------------------------------------------------------
            # 如果时间大于 60 ( 一分钟 )
            # ------------------------------------------------------------------
            else if ( $timej > 60 )
            {
                return floor( $timej / 60 ) . "分钟前";
            }
            else
            {
                return "刚刚";
            }
        }
        else
        {
            return date( 'Y-m-d H:i', $time );
        }
    }

    /**
    @brief      得到当前日期
    @param      int         $time :时间，默认为当前时间
    @param      string      $fmt :日期格式
    @retval     string
    @remarks    此範例返回："2015-02-26"
    @code{.unparsed}
    $this->Core_Date->getDate();
    @endcode
    **/
    public function getDate( $time = null , $fmt = 'Y-m-d' )
    {
        $times = ( $time ) ? $time : time();
        return date( $fmt , $times );
    }

    /**
    @brief      得到当前日期时间
    @param      int         $time :时间，默认为当前时间
    @param      string      $fmt :日期格式
    @retval     string
    @remarks    此範例返回："2008-10-12 10:36:48"，1229173896 是指定的时间戳。
    @code{.unparsed}
    $this->Core_Date->getTime( 1229173896 );
    @endcode
    @remarks    此範例返回："2008-10-12 10:36:48"
    @code{.unparsed}
    $this->Core_Date->getTime();
    @endcode
    **/
    public function getTime( $time = null , $fmt = 'Y-m-d H:i:s' )
    {
        return self::getDate( $time , $fmt );
    }

    /**
    @brief      计算日期天数差
    @param      string      $date1 :如 "2005-10-20"
    @param      string      $date2 :如 "2005-10-10"
    @retval     int
    @remarks    此範例返回：10。
    @code{.unparsed}
    $this->Core_Date->dateDiff( '2005-10-20' , '2005-10-10' );
    @endcode
    **/
    public function dateDiff( $date1 , $date2 )
    {
        $DateList1 = explode( "-" , $date1 );
        $DateList2 = explode( "-" , $date2 );
        $d1 = mktime( 0 , 0 , 0 , $DateList1[1] , $DateList1[2] , $DateList1[0] );
        $d2 = mktime( 0 , 0 , 0 , $DateList2[1] , $DateList2[2] , $DateList2[0] );
        $Days = round( ( $d1 - $d2 ) / 3600 / 24 );
        return $Days;
    }

    /**
    @brief      计算日期加天数后的日期
    @param      string      $date :如 "2005-10-20"
    @param      int         $day  :如 6
    @retval     string
    @remarks    此範例返回：2005-10-26。
    @code{.unparsed}
    $this->Core_Date->dateAddDay( '2005-10-20' , 6 );
    @endcode
    **/
    public function dateAddDay( $date , $day )
    {
        $daystr = "+$day day";
        $dateday = date( "Y-m-d" , strtotime( $daystr , strtotime( $date ) ) );
        return $dateday;
    }

    /**
    @brief      计算日期減天数后的日期
    @param      string      $date :如 "2005-10-20"
    @param      int         $day  :如 10
    @retval     string
    @remarks    此範例返回：2005-10-10。
    @code{.unparsed}
    $this->Core_Date->dateDecDay( '2005-10-20' , 10 );
    @endcode
    **/
    public function dateDecDay( $date , $day )
    {
        $daystr = "-$day day";
        $dateday = date( "Y-m-d" , strtotime( $daystr , strtotime( $date ) ) );
        return $dateday;
    }

    /**
    @brief      比较两个时间
    @param      string      $timeA :格式如 "2006-10-12" 或 "2006-10-12 12:30" 或 "2006-10-12 12:30:50"
    @param      string      $timeB :同上
    @retval     int
    @remarks    此範例返回：1。
    @code{.unparsed}
    $this->Core_Date->compareTiem( '2006-10-12' , '2006-10-11' );
    @endcode
    **/
    public function compareTiem( $timeA , $timeB )
    {
        $a = strtotime( $timeA );
        $b = strtotime( $timeB );
        if ( $a > $b )
        {
            return 1;
        }
        else if( $a == $b )
        {
            return 0;
        }
        else
        {
            return -1;
        }
    }

    /**
    @brief      计算时间a减去时间b的差值
    @param      string      $timeA :格式如 "2006-10-12" 或 "2006-10-12 12:30" 或 "2006-10-12 12:30:50"
    @param      string      $timeB :同上
    @retval     float       实数的小时,如"2.3333333333333"小时
    @remarks    此範例返回：2。
    @code{.unparsed}
    $this->Core_Date->dateTimeDiff( '2005-10-20 10:00:00' , '2005-10-20  08:00:00' );
    @endcode
    **/
    public function dateTimeDiff( $timeA , $timeB )
    {
        $a = strtotime( $timeA );
        $b = strtotime( $timeB );
        $c = $a - $b;
        $c = $c / 3600;
        return $c;
    }

    /**
    @brief      日期反推星期几
    @param      String      $date :格式如 "2006-10-12"
    @param      String      $_type :預設為 constrict(精簡)，可選 norm(正常)
    @retval     String
    @remarks    此範例返回：四。
    @code{.unparsed}
    $this->Core_Date->getChweek( '2015-02-26' );
    @endcode
    @remarks    此範例返回：星期四。
    @code{.unparsed}
    $this->Core_Date->getChweek( '2015-02-26' , 'norm' );
    @endcode
    **/
    public function getChweek( $date , $_type = 'constrict' )
    {
        # ----------------------------------------------------------------------
        # 初始化星期变数
        # ----------------------------------------------------------------------
        $week = array( "日" , "一" , "二" , "三" , "四" , "五" , "六" );
        # ----------------------------------------------------------------------
        # 分离出年月日以便製作时戳
        # ----------------------------------------------------------------------
        $dates = explode( " " , $date );
        if ( $dates != $date && isset( $dates[0] ) )
        {
            list( $Y , $M , $D ) = explode( "-" , $dates[0] );
        }
        else
        {
            list( $Y , $M , $D ) = explode( "-" , $date );
        }
        # ----------------------------------------------------------------------
        # 如果是 constrict 代表使用缩写
        # ----------------------------------------------------------------------
        switch( $_type )
        {
            case "constrict":
                return $week[date("w", mktime( 0 , 0 , 0 , $M , $D , $Y ) )];
                break;
            case "norm":
            default:
                return "星期" . $week[date( "w" , mktime( 0 , 0 , 0 , $M , $D , $Y ) )];
        }
    }

    /**
    @brief      計算傳入的小時，反轉為秒數
    @param      Int         $hour   小時
    @retval     Int
    @remarks    此範例返回：3600。
    @code{.unparsed}
    $this->Core_Date->hour2Send( 1 );
    @endcode
    **/
    public function hour2Send( $hour )
    {
        if ( ! is_numeric( $hour ) )
        {
            return false;
        }
        else
        {
            return ( floatval( $hour ) ) * 3600;
        }
    }
}