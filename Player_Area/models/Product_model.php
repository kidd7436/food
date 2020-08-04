<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Product Model ( 商品管理模組 )
**/

class Product_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      商品明細轉二維
    **/
    public function arrToOne( )
    {
        $arr = array();
        # ----------------------------------------------------------------------
        # 取出文字檔
        # ----------------------------------------------------------------------
        $filename = DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR , array( "Player_Area" , "public" , "data" ,"product.txt" ) );
        if( file_exists( $filename ) )
        {
            $dataArr = file_get_contents( $filename );
            $dataArr = json_decode( $dataArr, true );
        }
        # ----------------------------------------------------------------------
        # 埅呆
        # ----------------------------------------------------------------------
        if( $dataArr )
        {
            foreach ( $dataArr as $key => $val) 
            {
                if( is_array( $val ) ) 
                {
                    $arr = array_merge( $arr, $val );
                } 
                else
                {
                    $arr[] = $val;
                }
            }
        }
        return $arr;
    }
}