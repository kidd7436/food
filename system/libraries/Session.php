<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Session 類別，將所有 Session 存放置資料庫。
  @version      1.0.0
  @date         2015-03-01
  @since        1.0.0 -> 新增此新類別。
**/

class Session
{
    /**
    @brief      使用者網路資訊 proxy
    **/
    public $proxy = "";

    /**
    @brief      使用者網路資訊 ip
    **/
    public $ip = "";

    /**
    @brief      以下為瀏覽器資訊
    **/
    public $agent;

    /**
    @cond       建構子
    @remarks    利用建構子處理 session_set_save_handler
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # session_set_save_handler
        # ----------------------------------------------------------------------
        session_set_save_handler
        (
            array( $this, "sess_open" ) ,
            array( $this, "sess_close" ) ,
            array( $this, "sess_read" ) ,
            array( $this, "sess_write" ) ,
            array( $this, "sess_destroy" ) ,
            array( $this, "sess_gc" )
        );
        session_start();
    }
    /**
    @endcond
    **/

    /**
    @brief      Session_Start 時會被呼叫
    **/
    public function sess_open( $save_path , $session_name )
    {
        return true;
    }

    /**
    @brief      $_Session 在頁面結束的處理動作
    **/
    public function sess_close()
    {
        return true;
    }

    /**
    @brief      Session_start 後的的處理動作
    **/
    public function sess_read( $key )
    {
      	# ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $value = "";
        # ----------------------------------------------------------------------
        # 組合SQL語法並查詢
        # ----------------------------------------------------------------------
        $sql = "!!SELECT `val` FROM `k_sessions` WHERE `sid` = :key";
        $result = Bootstart::$_lib['Core_Pdo_Driver']->query( $sql , array( ':key' => $key ) );
        $row = ( ! $result ) ? false : $result->fetch();

        if ( $row )
        {
            $value = $row["val"];
        }

      	return $value;
    }

    /**
    @brief      Session_start 調用會話數據要被寫入
    **/
    public function sess_write( $key , $val )
    {
      	# ----------------------------------------------------------------------
        # 先查詢要寫入的 SID 有沒有存在
        # ----------------------------------------------------------------------
        $result = Bootstart::$_lib['Core_Pdo_Driver']->query( "!!SELECT `uid` , `val` FROM `k_sessions` WHERE `sid` = :key" , array( ':key' => $key ) );
        $row = ( ! $result ) ? false : $result->fetch();
        if ( $row )
        {
            if ( ! empty( $val ) && is_null( $row['uid'] ) )
            {
                # --------------------------------------------------------------
                # 這邊僅更新 SESSION 的值、uid
                # --------------------------------------------------------------
                Bootstart::$_lib['Core_Pdo_Driver']->query_execute(
                    "UPDATE `k_sessions` SET `uid` = :uid , `val` = :value WHERE `sid` = :sid " ,
                    array( ':uid' => Core_LoadSession( 'id' ) , ':value' => $val , ':sid' => $key )
                );
            }
            else
            {
                # --------------------------------------------------------------
                # 這邊僅更新 SESSION 的值
                # --------------------------------------------------------------
                Bootstart::$_lib['Core_Pdo_Driver']->query_execute(
                    "UPDATE `k_sessions` SET `val` = :value WHERE `sid` = :sid " ,
                    array( ':value' => $val , ':sid' => $key )
                );
            }

            return TRUE;
        }
        else
        {
            $this->sess_insert( $key , $val );
        }

      	return 1;
    }

    /**
    @brief      session_destroy 會被呼叫處理
    **/
    public function sess_destroy( $key )
    {
        $sql = "DELETE FROM `k_sessions` WHERE `sid` = '$key'";
        Bootstart::$_lib['Core_Pdo_Driver']->query_execute( $sql );
    }

    /**
    @brief      $_session 過期處理
    **/
    public function sess_gc( $maxlifetime = 0 )
    {
        if ( $maxlifetime == 0 )
        {
            $maxlifetime = time() - get_cfg_var("session.gc_maxlifetime");
            $sql = "DELETE FROM `k_sessions` WHERE `lastupdate` < {$maxlifetime} ";
      	}
        else
        {
            $maxlifetime = time() - ( min( $maxlifetime , get_cfg_var( "session.gc_maxlifetime" ) ) );
            $sql = "DELETE FROM `k_sessions` WHERE `lastupdate` < {$maxlifetime} ";
      	}
        Bootstart::$_lib['Core_Pdo_Driver']->query_execute( $sql );

      	return true;
    }

    /**
    @brief      $_Session 新增的處理動作
    **/
  	public function sess_insert( $sid , $val = "" )
  	{
        # ----------------------------------------------------------------------
        # 宣告空變數
        # ----------------------------------------------------------------------
        $proxy = $ip = $host = $agent = $uri = ''; $timenow = time();
        # ----------------------------------------------------------------------
        # 當前登入者的 UserAgent
        # ----------------------------------------------------------------------
        $proxy = Bootstart::$_lib['Core_UserAgent']->proxy;
        # ----------------------------------------------------------------------
        # 當前登入者的 IP
        # ----------------------------------------------------------------------
        $ip = Bootstart::$_lib['Core_UserAgent']->ip;
        # ----------------------------------------------------------------------
        # 當前登入的伺服器名稱
        # ----------------------------------------------------------------------
        $host = isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["HTTP_HOST"];
        # ----------------------------------------------------------------------
        # 當前登入者的 UserAgent
        # ----------------------------------------------------------------------
        $agent = Bootstart::$_lib['Core_UserAgent']->agent;
        # ----------------------------------------------------------------------
        # get_uri(); 來源頁面,?用到
        # ----------------------------------------------------------------------
        $uri = $_SERVER['REQUEST_URI'];
        # ----------------------------------------------------------------------
        # 執行新增
        # ----------------------------------------------------------------------
        $sql = "INSERT INTO `k_sessions` ( `proxyip` , `ip` , `host` , `agent` , `uri` , `sid` , `val` , `lastupdate` , `createtime` )"
             . "VALUES ( INET_ATON(?) , INET_ATON(?) , INET_ATON(?) , ? , ? , ? , ? , ? , ? )";
        Bootstart::$_lib['Core_Pdo_Driver']->query_execute( $sql , array( $proxy , $ip , $host , $agent , $uri , $sid , $val , $timenow , $timenow ) );
    }

    /**
    @brief      $_Session 踢除相同身分的處理動作，只有 S_ 不會被踢
    **/
  	public function sess_KickMyself()
  	{
        if ( Core_LoadSession( 'subaccount' ) !== false )
        {
            $sql = "DELETE FROM `k_sessions` WHERE `uid` = '" . Core_LoadSession( 'subaccount' ) . "' ";
        }
        else
        {
            $sql = "DELETE FROM `k_sessions` WHERE `uid` = '" . Core_LoadSession( 'id' ) . "' ";
        }

      	Bootstart::$_lib['Core_Pdo_Driver']->query_execute( $sql );
    }

    /**
    @brief      計算當前線上總線上人數，不包含 我們的超級帳號
    **/
    public function sess_OnlineCount()
    {
        $sql = "SELECT COUNT( distinct A.`sid` ) AS count "
             . "FROM `k_sessions` A "
             . "LEFT JOIN `k_user` B ON( B.`id` = A.`uid` ) "
             . "WHERE A.`uid` != 2 AND A.`uid` != -1";
        $result = Bootstart::$_lib['Core_Pdo_Driver']->query( $sql );
        if( ! $result )
        {
            return 0;
        }
        else
        {
            $rs = $result->fetchColumn();
            return ( $rs === false )? 0 : $rs;
        }
    }

    /**
    @brief      踢掉 K_session 資料表 UID 為 NULL , 且 VAL 不為空的幽靈人口
    **/
    public function sess_KillGhost()
    {
        $sql = "DELETE FROM `k_sessions` WHERE `uid` IS NULL AND `val` <> '' ";
        Bootstart::$_lib['Core_Pdo_Driver']->query_execute( $sql );
    }

    /**
    @brief      登入處理 ( 更改當前登入者在 K_session 的資料 )
    **/
    public function sess_UserUpdate()
    {
        # ----------------------------------------------------------------------
        # 宣告空變數
        # ----------------------------------------------------------------------
        $proxy = $ip = $host = $agent = $uri = ''; $timenow = time();
        # ----------------------------------------------------------------------
        # 當前登入者的 UserAgent
        # ----------------------------------------------------------------------
        $proxy = Bootstart::$_lib['Core_UserAgent']->proxy;
        # ----------------------------------------------------------------------
        # 當前登入者的 IP
        # ----------------------------------------------------------------------
        $ip = Bootstart::$_lib['Core_UserAgent']->ip;
        # ----------------------------------------------------------------------
        # 當前登入的伺服器名稱
        # ----------------------------------------------------------------------
        $host = isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["HTTP_HOST"];
        # ----------------------------------------------------------------------
        # 當前登入者的 UserAgent
        # ----------------------------------------------------------------------
        $agent = Bootstart::$_lib['Core_UserAgent']->agent;
        # ----------------------------------------------------------------------
        # get_uri(); 來源頁面,?用到
        # ----------------------------------------------------------------------
        $uri = $_SERVER['REQUEST_URI'];
        # ----------------------------------------------------------------------
        # 執行修改
        # ----------------------------------------------------------------------
        Bootstart::$_lib['Core_Pdo_Driver']->query_execute
        (
            "UPDATE IGNORE `k_sessions` SET `lastupdate` = ? , `host` = INET_ATON(?) , `ip` = INET_ATON(?) , `proxyip` = INET_ATON(?) , `agent` = ? , `uri` = ? WHERE `sid` = ? " ,
            array( $timenow , $host , $ip , $proxy , $agent , $uri , session_id() )
        );
    }
}