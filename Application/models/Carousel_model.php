<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        Carousel Model ( 輪播圖模組 )
**/

class Carousel_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      取得所有輪播圖
    @retval     Mix / FALSE
    **/
    public function getCarousel( $_kinds = false )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `carousel` WHERE ";
        $sql .= ( $_kinds ) ? " `kinds` = '{$_kinds}'" : "1";
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
    @brief      取得指定的內容
    @retval     Mix / FALSE
    **/
    public function getCarouselById( $_id = 1 )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `carousel` WHERE `id` = {$_id} ";
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
    public function writeTxt( )
    {
        $carouselArr = array();
        $carouselArr = self::getCarousel( );

        $filename = DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR , array( "Player_Area" , "public" , "data" ,"carousel.txt" ) );
        $openProduc = array();

        foreach( $carouselArr as $key => $value )
        {
            if( $value[ "enabled" ] == 0 ) continue;
            $openProduc[ $value[ "kinds" ] ][] = $value;
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