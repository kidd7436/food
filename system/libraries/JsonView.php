<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        JsonView
  @version      1.0.0
  @date         2015-03-29 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

class JsonView extends View
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
    @param      Boolean     $Cha 資料有無中文
    @remarks    輸出結果 JSON 格式。
    @code{.unparsed}
    $this->Core_JsonView->render
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
    public function render( $data = array() , $Cha = false )
    {
        # ----------------------------------------------------------------------
        # mime 訂義標頭
        # ----------------------------------------------------------------------
        header( 'Content-type: application/json; charset=utf-8' );
        if ( $data == false )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 如果是除錯模式，就添加除錯資料
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT == 'development' && DEVIL_APP_DEBUGMETHOD == 'debugbar' )
        {
            $data['debug'] = Bootstart::$_debug->displayInfo( true );
        }
        # ----------------------------------------------------------------------
        # 將陣列資料轉為 JSON 格式
        # ----------------------------------------------------------------------
        $data = json_encode( $data );
        # ----------------------------------------------------------------------
        # 有包含中文字處理方法
        # ----------------------------------------------------------------------
        if ( $Cha )
        {
            preg_match_all( '/\\\\u[a-f\d]+/ims', $data, $match );
            if ( count( $match[0] ) === 0 ) return $data;
            foreach( $match[0] as $c )
            {
                $data = str_replace( $c , json_decode( '"' . $c . '"' ) , $data );
            }
            echo $data;
        }
        else
        {
            echo $data;
        }
    }
}