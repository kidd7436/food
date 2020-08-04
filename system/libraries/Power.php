<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        權限類，利用登入後存在於 SESSION 中的權限值判斷。
  @version      1.0.0
  @date         2015-02-28
  @since        1.0.0 -> 新增此新類別。
**/

class Power
{
    /**
    @brief      初始化權限值
    **/
    private $power = 0;

    /**
    @brief      緩存權限值
    **/
    public $Tmp_power = 0;

    /**
    @brief      所有權限值陣列
    **/
    protected $powerArr = array
    (
        # ----------------------------------------------------------------------
        # 【用戶管理】權限
        # ----------------------------------------------------------------------
        0 => 1
    );

    /**
    @cond       建構子
    @remarks    使用者登入後取出 Session中使用者的權限值
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 取出 SESSION 中的 pow 欄位值
        # ----------------------------------------------------------------------
        $this->power = Core_LoadSession( 'pow' );
    }
    /**
    @endcond
    **/

    /**
    @cond       解構子
    @remarks    程式結束時，將緩存的權限值歸0
    **/
    public function __destruct()
    {
        # ----------------------------------------------------------------------
        # 程式結束時，將緩存的權限值歸0
        # ----------------------------------------------------------------------
        $this->Tmp_power = 0;
    }
    /**
    @endcond
    **/

    /**
    @brief      取得當前的權限總值
    @retval     Int
    **/
    public function getPowerCode()
    {
        return $this->power;
    }

    /**
    @brief      分析當前權限值所包含的權限
    @param      Int     $code 權限值
    @retval     Array | False
    **/
    public function parsePower( $code )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $powerlist = array();
        # ----------------------------------------------------------------------
        # 轉換成二進制
        # ----------------------------------------------------------------------
        $code = decbin( $code );
        $num = strlen( $code );
        for ( $i = 0; $i < $num; $i++ )
        {
            if ( $code{$i} )
            {
                $pow = pow ( 2 , $num - $i - 1 );
                $key = array_search( $pow , $this->powerArr );
                $powerlist[$key] = $pow;
            }
        }

        return array_reverse( $powerlist , true );
    }

    /**
    @brief      檢查權限的合法性
    @param      Int     $code 權限值
    @retval     Bool
    **/
    public function isPowerCode( $code )
    {
        if ( isset( $this->powerArr[$code] ) )
        {
            $codeval = $this->powerArr[$code];
            # ------------------------------------------------------------------
            # 如果不是數字
            # ------------------------------------------------------------------
            if ( ! is_numeric( $codeval ) )
            {
                return false;
            }
            # ------------------------------------------------------------------
            # 如果不是數字該數字大於32位元版本的 INT_MAX 值 ( 2147483647 )
            # ------------------------------------------------------------------
            if ( $codeval > PHP_INT_MAX )
            {
                return false;
            }
            # ------------------------------------------------------------------
            # 如果指定的 權限值 大於 預設最高權限總值
            # ------------------------------------------------------------------
            if ( $codeval > $this->power )
            {
                return false;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
    @brief      判斷是否有權限，返回 0 代表沒有權限
    @param      Int     $code 指定的權限值
    @param      Int     $_tmp 利用其他緩存的
    @return     Int | Bool
    **/
    public function isPower( $code , $_tmp = false )
    {
        # ----------------------------------------------------------------------
        # 檢查權限合法性
        # ----------------------------------------------------------------------
        if ( ! $this->isPowerCode( $code ) )
        {
            return false;
        }
        else
        {
            # ------------------------------------------------------------------
            # $_tmp TRUE 時，表示不使用 SESSION 中的，因為需要編輯其他身份
            # ------------------------------------------------------------------
            if ( $_tmp )
            {
                $andval = $this->Tmp_power & $this->powerArr[$code];
            }
            # ------------------------------------------------------------------
            # 使用 SESSION 中的權限值
            # ------------------------------------------------------------------
            else
            {
                $andval = $this->power & $this->powerArr[$code];
            }

            return ( $this->powerArr[$code] === $andval ) ? true : false;
        }
    }

    /**
    @brief      添加权限
    @param      Int     $code 要添加的权限码
    @return     Bool    权限码不合法时返回false
    **/
    public function addPower( $code )
    {
        if ( ! $this->isPowerCode( $code ) )
        {
            return false;
        }
        $this->power = $this->power | $code;
        return true;
    }

    /**
    @brief      删除相应的权限
    @param      Int     $code 要删除的权限码
    @return     Bool    权限码不合法时返回false
    **/
    public function delPower( $code )
    {
        if ( ! $this->isPowerCode( $code ) )
        {
            return false;
        }
        $this->power = $this->power ^ $code;
        return true;
    }
}