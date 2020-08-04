<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Math 用來處理數字相關的功能類別。
  @version      1.0.0
  @date         2015-03-04
  @since        1.0.0 -> 新增此新類別。
**/

class Math
{
    /**
    @brief      將文件容量大小以位元組(bytes)為單位格式化，並加上適合的對應縮寫單位
    @param      Int         $num
    @param      Int         $precision 小數點位數
    @retval     String
    **/
    public function byte_format( $num , $precision = 3 )
    {
        if ( $num >= 1000000000000 )
        {
            $num = round( $num / 1099511627776 , $precision );
            $unit = 'TB';
        }
        elseif ( $num >= 1000000000 )
        {
            $num = round( $num / 1073741824, $precision );
            $unit = 'GB';
        }
        elseif ( $num >= 1000000 )
        {
            $num = round( $num / 1048576 , $precision );
            $unit = 'MB';
        }
        elseif ( $num >= 1000 )
        {
            $num = round( $num / 1024 , $precision );
            $unit = 'KB';
        }
        else
        {
            $unit = 'bytes';
            return number_format( $num ) . ' ' . $unit;
        }

        return number_format( $num , $precision ) . ' ' . $unit;
    }

    /**
    @brief      無條件進位
    @param      Int         $v
    @param      Int         $precision 小數點位數 - 預設為3位
    @retval     String
    @remarks    返回: 1.01
    @code{.unparsed}
    $this->Core_Math->ceilDec( 1.0001 , 2 );
    @endcode
    **/
    public function ceilDec( $v , $precision = 3 )
    {
        $c = pow( 10 , $precision );
        return ceil( $v * $c ) / $c;
    }

    /**
    @brief      产生一个随机数
    @param      Int         $min 最小
    @param      Int         $max 最大
    @return     Int
    @remarks    返回1-100之间的整数
    @code{.unparsed}
    $this->Core_Math->randNum( 1 , 100 );
    @endcode
    @remarks    返回一个随机的整数
    @code{.unparsed}
    randNum();
    @endcode
    **/
    public function randNum( $min = null , $max = null )
    {
        mt_srand( (double)microtime() * 1000000 );
        if ( $min === null || $max === null )
        {
            return mt_rand();
        }
        else
        {
            return mt_rand( $min , $max );
        }
    }

    /**
    @brief      無條件捨去
    @param      Int         $v
    @param      Int         $precision 小數點位數 - 預設為3位
    @retval     String
    @remarks    返回: 1.01
    @code{.unparsed}
    $this->Core_Math->floorDec( 1.11111 , 2 );
    @endcode
    **/
    public function floorDec( $v , $precision = 3 )
    {
        $c = pow( 10 , $precision );
        return floor( $v * $c ) / $c;
    }

    /**
    @brief      BCmath 運算群
    @param      Float|Int   $_value1 數字
    @param      Float|Int   $_value2 數字
    @param      String      $_type 運算類型 - 預設加法
    @param      Int         $_precision 精準度
    @remarks    類型：
    - add ( 加法 )
    - sub ( 減法 )
    - mul ( 乘法 )
    - div ( 除法 )
    - mod ( 求餘數 )
    - pow ( 乘冪 )
    - sqrt ( 平方根 )
    - comp ( 以任意精度比較 )
    @return     String
    **/
    public function hel_bcmath( $_value1 , $_value2 , $_type = 'add' , $_precision = null )
    {
        # ----------------------------------------------------------------------
        # 設定小數精準度
        # ----------------------------------------------------------------------
        if ( $_precision === null )
        {
            bcscale( DEVIL_APP_DECPNUM );
        }
        else
        {
            bcscale( $_precision );
        }
        # ----------------------------------------------------------------------
        # 依照要運算的方式來處理
        # ----------------------------------------------------------------------
        switch( $_type )
        {
            # ------------------------------------------------------------------
            # 加法
            # ------------------------------------------------------------------
            default:
            case 'add':
                return bcadd( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 減法
            # ------------------------------------------------------------------
            case 'sub':
                return bcsub( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 乘法
            # ------------------------------------------------------------------
            case 'mul':
                return bcmul( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 除法
            # ------------------------------------------------------------------
            case 'div':
                return bcdiv( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 求餘數
            # ------------------------------------------------------------------
            case 'mod':
                return bcmod( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 乘冪
            # ------------------------------------------------------------------
            case 'pow':
                return bcpow( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 平方根
            # ------------------------------------------------------------------
            case 'sqrt':
                return bcsqrt( $_value1 , $_value2 );
                break;
            # ------------------------------------------------------------------
            # 以任意精度比較
            # ------------------------------------------------------------------
            case 'comp':
                return bccomp( $_value1 , $_value2 );
                break;
        }
    }
}