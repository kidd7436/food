<?php  if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Login Model ( 登入模組 )
**/
class Login_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      計算當前線上人數 Log 紀錄
    @param      Int     $member_limit
    **/
    public function Login_CountSession( $_member_limit )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "!!SELECT COUNT( `uid` ) AS COUNT FROM `k_sessions` WHERE `uid` != 2 AND `uid` != -1";
        # ----------------------------------------------------------------------
        # 執行查詢結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql );
        # ----------------------------------------------------------------------
        # 接收回傳值
        # ----------------------------------------------------------------------
        $row = ( $result ) ? $result->fetch() : false;
        # ----------------------------------------------------------------------
        # 判斷是否大於最大上線人數
        # ----------------------------------------------------------------------
        if ( $row && $row['COUNT'] > $_member_limit )
        {
            # ------------------------------------------------------------------
            # 更新本月最大上線人數
            # ------------------------------------------------------------------
            $dt = date( "Y-m-d H:i:s" );
            # ------------------------------------------------------------------
            # 組合更新語法
            # ------------------------------------------------------------------
            $sql = "INSERT INTO `k_config` ( `id`, `val`, `updateid` ,`updatedt` ) VALUES ( ? , ? , ? , ? ) "
                 . "ON DUPLICATE KEY UPDATE `val` = ? , `updateid` = '1' , `updatedt` = ? ";
            # ------------------------------------------------------------------
            # 執行新增病直接回傳結果
            # ------------------------------------------------------------------
            $this->db->query_execute
            (
                $sql ,
                array( 'member_limit' , $row['COUNT'] , 1 , $dt , $row['COUNT'] , $dt )
            );
        }
    }

    /**
    @brief      新增登入 Log 紀錄
    @param      String   $_array
    @param      String   SESSION_ID
    **/
    public function Login_InsertLog( $_array )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $_ip = $_beginip = $_endip = $_country = $_area = $_server = $_proxyip = '';
        # ----------------------------------------------------------------------
        # IP
        # ----------------------------------------------------------------------
        $_ip = $_array['ip'];
        # ----------------------------------------------------------------------
        # 純真IP類處理後的起始IP網段
        # ----------------------------------------------------------------------
        $_beginip = $_array['beginip'];
        # ----------------------------------------------------------------------
        # 純真IP類處理後的結束IP網段
        # ----------------------------------------------------------------------
        $_endip = $_array['endip'];
        # ----------------------------------------------------------------------
        # 所在國家
        # ----------------------------------------------------------------------
        $_country = $_array['country'];
        # ----------------------------------------------------------------------
        # 所在的ISP
        # ----------------------------------------------------------------------
        $_area = $_array['area'];
        # ----------------------------------------------------------------------
        # 取得當前伺服器的 IP
        # ----------------------------------------------------------------------
        $_server = $_SERVER["SERVER_ADDR"];
        # ----------------------------------------------------------------------
        # 如果有使用代理伺服器 ( Proxy )
        # ----------------------------------------------------------------------
        $_proxyip = Bootstart::$_lib['Core_UserAgent']->proxy;
        # ----------------------------------------------------------------------
        # 新增一比登入紀錄
        # ----------------------------------------------------------------------
        $this->db->query_execute
        (
            "INSERT INTO `k_log` ( `user_id` , `server` , `ip` , `proxyip` , `beginip` , `endip` , `country` , `area` , `logindt` , `agent` )
             VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )" ,
             array( Core_LoadSession( 'id' ) , $_server , $_ip , $_proxyip , $_beginip , $_endip , $_country , $_area , time() , Bootstart::$_lib['Core_UserAgent']->agent )
        );
    }

    /**
    @brief      登入處理 ( 邏輯處理 )
    @param      String   $username 帳號
    @param      String   $pass 密碼
    @retval     Mix
    **/
    public function Login_Result( $username , $pass )
    {
        # ----------------------------------------------------------------------
        # 先取出帳號有沒有存在
        # ----------------------------------------------------------------------
        $sql = "!!SELECT `id` , `pow` , `disabled` , `account` , `pass` , `logcount` , `failcount` , `name` FROM `k_user` WHERE `account` = ? AND `enabled` = 1 LIMIT 1 ";
        $result = $this->db->query( $sql , array( $username ) );
        # ----------------------------------------------------------------------
        # 如果資料有存在再做 fetch 處理
        # ----------------------------------------------------------------------
        $row = ( $result ) ? $result->fetch() : false;
        # ----------------------------------------------------------------------
        # 如果帳號有存在再比對密碼是否正確。
        # ----------------------------------------------------------------------
        if ( $row )
        {
            # ------------------------------------------------------------------
            # 如果密碼是正確的，就回傳資料
            # ------------------------------------------------------------------
            if ( md5( $pass ) == $row['pass'] )
            {
                return array
                (
                    'User_Count' => 1 ,
                    'User_Data' => array
                    (
                        "id" => $row['id'] ,
                        "pow" => $row['pow'] ,
                        "disabled" => $row['disabled'] ,
                        "account" => $row['account'] ,
                        "logcount" => $row['logcount'] ,
                        "failcount" => $row['failcount'] ,
                        "name" => $row['name'] ,
                    )
                );
            }
            # ------------------------------------------------------------------
            # 帳號有存在，但密碼錯誤的時候需要累計錯誤次數
            # ------------------------------------------------------------------
            else
            {
                # --------------------------------------------------------------
                # 取出全域設定中的 - 密碼容錯次數
                # --------------------------------------------------------------
                $login_fail_count = intval( Bootstart::$_mod['KConfig_Model']->Get( 'login_fail_count' ) );
                # --------------------------------------------------------------
                # 若當前帳號的錯誤次數 >= 密碼容錯次數
                # --------------------------------------------------------------
                if ( $row['failcount'] < $login_fail_count )
                {
                    # ----------------------------------------------------------
                    # 如果此次累加後等於系統設定的密碼錯誤次數，就停用當前帳號
                    # ----------------------------------------------------------
                    $this->Login_Fail_UserUpdate( $row['id'] , $row['failcount'] , time() , Bootstart::$_lib['Core_UserAgent']->ip );
                }
                # --------------------------------------------------------------
                # 如果當前次數 +1 會 >= 密碼容錯次數，就返回密码输入太多次错误的回傳值
                # --------------------------------------------------------------
                if ( $login_fail_count && ( ( $row['failcount'] + 1 ) >= $login_fail_count ) )
                {
                    # ----------------------------------------------------------
                    # 回傳 isset 因為利用 Ajax
                    # ----------------------------------------------------------
                    return 'isset';
                }
                else
                {
                    # ----------------------------------------------------------
                    # 回傳失敗
                    # ----------------------------------------------------------
                    return false;
                }
            }
        }
        else
        {
            # ------------------------------------------------------------------
            # 回傳失敗
            # ------------------------------------------------------------------
            return false;
        }
    }

    /**
    @brief      登入處理 ( 更改當前登入者在 K_SESSION 的 UID )
    @param      String      $uid 使用者id
    @param      String      $sessionId 使用者的 session_id
    **/
    public function Login_UpdateUid( $uid , $sessionId )
    {
        $this->db->query_execute
        (
            "UPDATE `k_sessions` SET `uid` = :uid WHERE `sid` = :sessionId " ,
            array( ':uid' => $uid , ':sessionId' => $sessionId )
        );
    }

    /**
    @brief      登入處理 ( 更改當前登入者在 K_user 的 次數、IP、最後登入時間 )
    @param      Int         $_uid 使用者id
    @param      Int         $_logcount 登入次數
    @param      Int         $_lastlogdt 最後登入日期
    @param      Int         $_lastlogip 最後登入IP
    **/
    public function Login_UserUpdate( $_uid , $_logcount , $_lastlogdt , $_lastlogip )
    {
        $this->db->query_execute
        (
            "UPDATE `k_user` SET `logcount` = :logcount , `lastlogdt` = :lastlogdt , `lastlogip` = :lastlogip , `failcount` = 0 WHERE `id` = :uid " ,
            array( ':logcount' => ( $_logcount + 1 ) , ':lastlogdt' => $_lastlogdt , ':lastlogip' => $_lastlogip , ':uid' => $_uid )
        );
    }

    /**
    @brief      登入失敗處理 ( 更改當前登入者在 K_user 的 失敗次數 )
    @param      Int         $_uid 使用者id
    @param      Int         $_failcount 登入次數
    @param      Int         $_lastlogdt 最後登入日期
    @param      Int         $_lastlogip 最後登入IP
    **/
    public function Login_Fail_UserUpdate( $_uid , $_failcount , $_lastlogdt , $_lastlogip )
    {
        # ----------------------------------------------------------------------
        # 取出全域設定中的 - 密碼容錯次數
        # ----------------------------------------------------------------------
        $login_fail_count = intval( Bootstart::$_mod['KConfig_Model']->Get('login_fail_count') );
        # ----------------------------------------------------------------------
        # 如果【密碼容錯次數】> 0 且當前使用者的錯誤次數 > 密碼容錯次數
        # ----------------------------------------------------------------------
        if ( $login_fail_count && ( $_failcount >= $login_fail_count || ( $_failcount + 1 ) == $login_fail_count ) )
        {
            $this->db->query_execute
            (
                "UPDATE `k_user` SET `failcount` = :failcount , `lastlogdt` = :lastlogdt , `lastlogip` = :lastlogip, `disabled` = 1 WHERE `id` = :uid " ,
                array( ':failcount' => ( $_failcount + 1 ) , ':lastlogdt' => $_lastlogdt , ':lastlogip' => $_lastlogip , ':uid' => $_uid )
            );
        }
        else
        {
            $this->db->query_execute
            (
                "UPDATE `k_user` SET `failcount` = :failcount , `lastlogdt` = :lastlogdt , `lastlogip` = :lastlogip WHERE `id` = :uid " ,
                array( ':failcount' => ( $_failcount + 1 ) , ':lastlogdt' => $_lastlogdt , ':lastlogip' => $_lastlogip , ':uid' => $_uid )
            );
        }
    }
}