<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        XmlView
  @version      1.0.0
  @date         2015-03-29 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

class XmlView extends View
{
    /**
    @cond       建構子
    **/
    public function __construct(  )
    {
        //
    }
    /**
    @endcond
    **/

    /**
    @brief      將陣列轉換為 JSON，並秀出處理完的資料 ( JSON 頁面 )
    @param      Array       $data 資料陣列
    @param      String      $rootName 根結點名稱
    @param      Bool        $header 設定標頭，通常用於第一次產生時使用 true，其餘皆為 false
    @remarks    輸出結果 XML 格式。
    @code{.unparsed}
    $this->Core_XmlView->render
    (
        array
        (
            'code' => 200 ,
            'message' => 'success',
            'data' => array
            (
                array( 'id' => 100 , 'name' => 'vam' ) , array( 'id' => 200 , 'name' => 'vam2' ) 
            )
        )
    );
    @endcode
    **/
    public function render( $data = array() , $rootName = 'root' , $header = true )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $xml = $attr = '';
        # ----------------------------------------------------------------------
        # 設定標頭，通常用於第一次產生
        # ----------------------------------------------------------------------
        if ( $header )
        {
            # ------------------------------------------------------------------
            # mime 訂義標頭
            # ------------------------------------------------------------------
            header( 'Content-type: application/xml; charset=utf-8' );
            # ------------------------------------------------------------------
            # 組合XML資料
            # ------------------------------------------------------------------
            $xml = "<?xml version='1.0' encoding='utf-8'?>\n";
            $xml .= "<{$rootName}>\n";
            $xml .= self::render( $data , null , false );
            $xml .= "</{$rootName}>\n";
            exit( $xml );
        }
        else
        {
            # ------------------------------------------------------------------
            # 處理
            # ------------------------------------------------------------------
            foreach( $data as $key => $value )
            {
                if ( is_numeric( $key ) )
                {
                    $attr = "id='{$key}'";
                    $key = "item";
                }
                $xml .= "<{$key} {$attr}>";
                $xml .= is_array( $value ) ? self::render( $value , null , false ) : $value;
                $xml .= "</{$key}>\n";
            }
        }

        return $xml;
    }
}