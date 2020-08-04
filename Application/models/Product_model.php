<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Product Model ( 商品模組 )
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
    @brief      取得所有商品
    @retval     Mix / FALSE
    **/
    public function getProductByType( $_type = 1 )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `product` WHERE " ;
        $sql .= ( $_type > 0 ) ? "`type` = {$_type}" : 1 ;
        $sql .= " ORDER BY `type`,`sort` ASC " ;
        # ----------------------------------------------------------------------
        # 執行查詢結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql );
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
            return $result->fetchAll();
        }
    }

    /**
    @brief      取得所有商品
    @retval     Mix / FALSE
    **/
    public function getProductById( $_id = 1 )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `product` WHERE `id` = {$_id} ";
        # ----------------------------------------------------------------------
        # 執行查詢結果
        # ----------------------------------------------------------------------
        $result = $this->db->query( $sql );
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
    @brief      寫檔
    @retval     Mix / FALSE
    **/
    public function writeTxt( $_type = 1 )
    {
        $productArr = array();
        $productArr = self::getProductByType( 0 );

        //$filename = DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR , array( "Player_Area" , "public" , "data" ,"product.txt" ) );
        $filename = dirname( dirname( dirname(__FILE__) ) ) . DIRECTORY_SEPARATOR . "Player_Area"  . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "product.txt";
        $openProduc = array();
        foreach( $productArr as $key => $value )
        {
            if( $value[ "enabled" ] == 0 ) continue;
            $openProduc[ $value[ "type" ] ][ $value[ "title" ] ] = $value;
        }
        $openProduc = json_encode($openProduc);
        if( !file_exists( $filename ) )
        {
            $fp = fopen( $filename, 'w' );
            fclose($fp);
            chmod($filename, 0700);
        }
        file_put_contents( $filename , $openProduc );
    }
}