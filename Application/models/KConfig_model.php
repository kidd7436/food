<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        KConfig Model ( 全域設定模組 )
**/

final class KConfig_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      取出設定值
    @param      String
    @retval     String      $val
    **/
    public function Get( $id )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $ret = $sql = '';
        # ----------------------------------------------------------------------
        # 組合sql語法
        # ----------------------------------------------------------------------
        $sql = "!!SELECT `val` FROM `k_config` WHERE `id` = ? LIMIT 0 , 1";
        # ----------------------------------------------------------------------
        # 取出指定的設定資料
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql , array( $id ) );
        # ----------------------------------------------------------------------
        # 防呆判斷
        # ----------------------------------------------------------------------
        if ( ! $result )
        {
            return $ret;
        }
        else
        {
            $result_user = $result->fetch();
            # ------------------------------------------------------------------
            # 判斷結果
            # ------------------------------------------------------------------
            if ( ! $result_user )
            {
                return $ret;
            }
            else
            {
                return $result_user['val'];
            }
        }
    }

    /**
    @brief      取出指定陣列的設定值
    @param      Array       $idArr K_config 的id欄位
    @return     Array
    **/
    public function GetList( $idArr = array() )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $TmpidArr = $respanArr = array(); $id = '';
        # ----------------------------------------------------------------------
        # 迴圈整理資料
        # ----------------------------------------------------------------------
        foreach( $idArr as $k => $v )
        {
            $TmpidArr[] = '"' . $v . '"';
        }
        $id = implode( ',' , $TmpidArr );
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "!!SELECT `id`,`val` FROM `k_config` WHERE `id` IN( {$id} ) ";
        # ----------------------------------------------------------------------
        # 執行查詢結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql );
        # ----------------------------------------------------------------------
        # 防呆判斷 ( 有資料才跑迴圈 )
        # ----------------------------------------------------------------------
        if ( $result )
        {
            while( $row = $result->fetch() )
            {
                $respanArr[$row['id']] = $row['val'];
            }
        }

        return $respanArr;
    }

    /**
    @brief      儲存設定值
    @param      String      $id
    @param      String      $value
    @param      Boolean     $_auto 判斷是系統自動設定，還是人工手動
    **/
    public function Set( $id , $value , $_auto = FALSE )
    {
      	# ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $dt = date( "Y-m-d H:i:s" );
        # ----------------------------------------------------------------------
        # 如果是自動設定，UID 就用 0
        # ----------------------------------------------------------------------
        $uid = ( $_auto ) ? 0 : Core_LoadSession( 'id' );
        # ----------------------------------------------------------------------
        # 產生SQL語法，並處理更新
        # ----------------------------------------------------------------------
        $sql = "INSERT INTO `k_config` ( `id`, `val`, `updateid` ,`updatedt` , `updateip` ) VALUES ( :id , :value , :updateid , :updatedt , INET_ATON(:updateip) ) "
             . "ON DUPLICATE KEY UPDATE `val` = :value , `updateid` = :updateid , `updatedt` = :updatedt , `updateip` = INET_ATON(:updateip) ";
        # ----------------------------------------------------------------------
        # 回傳執行結果
        # ----------------------------------------------------------------------
        return $this->db->query_execute
        (
            $sql ,
            array( ':id' => $id , ':value' => strval( $value ) , ':updateid' => $uid , ':updatedt' => $dt , ':updateip' => Bootstart::$_lib['Core_UserAgent']->ip )
        );
    }

    /**
    @brief      一次取出 k_config 所有資料
    @return     Array
    **/
    public function Supporter_GetAll_K_config()
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $result = $this->db->query( "!!SELECT `id` , `val` FROM `k_config`" );
        # ----------------------------------------------------------------------
        # 利用 fetchAll() 來接收指定的資料集，這邊有錯誤會回傳空陣列 array()
        # ----------------------------------------------------------------------
        $row = $result->fetchAll();
        # ----------------------------------------------------------------------
        # array() === false
        # ----------------------------------------------------------------------
        if ( ! $row )
        {
            return FALSE;
        }
        else
        {
            # ------------------------------------------------------------------
            # 初始化變數
            # ------------------------------------------------------------------
            $response = array();
            # ------------------------------------------------------------------
            # 整理資料
            # ------------------------------------------------------------------
            foreach ( $row as $val )
            {
                $response[$val['id']] = $val['val'];
            }

            return $response;
        }
    }
}