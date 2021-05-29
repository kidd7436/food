<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        News Model ( 公告管理模組 )
**/

class News_Model extends Model
{
    /**
    @brief      建構子
    **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
    @brief      取得所有公告
    @retval     Mix / FALSE
    **/
    public function getNewsAll( )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `news` WHERE 1";
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
    @brief      取得指定的公告
    @retval     Mix / FALSE
    **/
    public function getNewsById( $_id = 0 )
    {
        # ----------------------------------------------------------------------
        # 組合SQL語法
        # ----------------------------------------------------------------------
        $sql = "SELECT * FROM  `news` WHERE `id` = {$_id}";
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
        $newsArr = array();
        $newsArr = self::getNewsAll( );

        $filename = dirname( dirname( dirname(__FILE__) ) ) . DIRECTORY_SEPARATOR . "Player_Area"  . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "news.txt";

        $news = "";
        foreach( $newsArr as $key => $value )
        {
            if( $value[ "dts" ] < time() && $value[ "dte" ] > time() && $value[ "enabled" ] == 1 )
            {
                $news .= $value[ "content" ].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        if( file_exists( $filename ) )
        {
            $fp = fopen( $filename, 'w' );
            fclose($fp);
            chmod($filename, 0777);
        }
        file_put_contents( $filename , $news );
    }

}