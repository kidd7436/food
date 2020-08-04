<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Users Model ( 使用者模組 )
**/

class Users_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      取得當前登入者資料
    @retval     Mix / FALSE
    **/
    public function Get_CurrentUsers( $_uid = null )
    {
        # ----------------------------------------------------------------------
        # 若沒有指定使用者ID則使用當前的登入者資料
        # ----------------------------------------------------------------------
        $_uid = ( $_uid === null ) ? Core_LoadSession( 'id' ) : $_uid;
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "!!SELECT * FROM  `k_user` WHERE `id` = ? LIMIT 0 , 1";
        # ----------------------------------------------------------------------
        # 取回執行後的結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql , array( $_uid ) );
        # ----------------------------------------------------------------------
        # 防呆判斷
        # ----------------------------------------------------------------------
        if ( ! $result )
        {
            return false;
        }
        else
        {
            # ------------------------------------------------------------------
            # 回傳資料集
            # ------------------------------------------------------------------
            return $result->fetch();
        }
    }

    /**
    @brief      取得當前登入者是否被禁止登入
    @return     INT / FALSE
    **/
    public function getStates( $_uid = null )
    {
        # ----------------------------------------------------------------------
        # 若沒有指定使用者ID則直接回傳錯誤
        # ----------------------------------------------------------------------
        if ( $_uid === null )
        {
            return false;
        }
        else
        {
            # ------------------------------------------------------------------
            # 取出指定的使用者ID資料，若沒有資料則回傳錯誤
            # ------------------------------------------------------------------
            $result = $this->Get_CurrentUsers( $_uid );
            # ------------------------------------------------------------------
            # 防呆判斷
            # ------------------------------------------------------------------
            if ( ! $result )
            {
                return false;
            }
            else
            {
                return $result['disabled'];
            }
        }
    }

    /**
    @brief      取得指定的使用者資料
    @retval     Mix / FALSE
    **/
    public function getUser( $_account = null )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "!!SELECT * FROM  `k_user` WHERE `account` = ? LIMIT 0 , 1";
        # ----------------------------------------------------------------------
        # 取回執行後的結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql , array( $_account ) );
        # ----------------------------------------------------------------------
        # 防呆判斷
        # ----------------------------------------------------------------------
        if ( ! $result )
        {
            return false;
        }
        else
        {
            # ------------------------------------------------------------------
            # 回傳資料集
            # ------------------------------------------------------------------
            return $result->fetch();
        }
    }
}