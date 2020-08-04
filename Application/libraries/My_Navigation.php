<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH') ) exit( 'No direct script access allowed' );
    /**
    @brief      選單
    **/

class My_Navigation
{
    /**
    @brief      產生功能選單
    **/
    public function generate( )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $navigation = ''; 
        $pow = $navigationArray = array();
        # ----------------------------------------------------------------------
        # 取出該帳號權限
        # ----------------------------------------------------------------------
        $pow = Bootstart::$_lib['My_Power']->parsePower( Core_LoadSession( 'pow' ) );
        # ----------------------------------------------------------------------
        # 重新排序
        # ----------------------------------------------------------------------
        ksort( $pow );
        # ----------------------------------------------------------------------
        # 迴圈產生選單
        # ----------------------------------------------------------------------
        foreach ( $pow as $k => $val )
        {
            # ------------------------------------------------------------------
            # 判斷有無權限
            # ------------------------------------------------------------------
            if ( Bootstart::$_lib['My_Power']->isPower( $k ) )
            {
                # --------------------------------------------------------------
                # 判斷有沒有存在( 防呆 )
                # --------------------------------------------------------------
                if ( isset( Bootstart::$_lib['My_Power']->powerArr_cn[ $val ] ) )
                {
                    # ----------------------------------------------------------
                    # 取得圖片配色
                    # ----------------------------------------------------------
                    $_klass = Bootstart::$_lib['My_Power']->powerArr_cn[ $val ]['klass'];
                    # ----------------------------------------------------------
                    # 選單的控制器放到變數中
                    # ----------------------------------------------------------
                    $_action = Bootstart::$_lib['My_Power']->powerArr_cn[ $val ]['action'];
                    # ----------------------------------------------------------
                    # 選單的名稱
                    # ----------------------------------------------------------
                    $_subject = Bootstart::$_lib['My_Power']->powerArr_cn[ $val ]['subject'];
                    # ----------------------------------------------------------
                    # 當前選項是不是當前呼叫的控制器
                    # ----------------------------------------------------------
                    $_isActive = ( Bootstart::$_Controller == $_action ) ? 'class="current"' : '';
                    # ----------------------------------------------------------
                    # 依照不同的層級來處理選單的產生方式
                    # ----------------------------------------------------------
                    $navigationArray[ ] = '<li '.$_isActive.'><a href="' . DEVIL_APP_Url . $_action . '"><i class="' . $_klass . '"></i> ' . $_subject . '</a></li>';
                }
            }
        }
        ksort( $navigationArray );
        # ----------------------------------------------------------------------
        # 整理回傳資料
        # ----------------------------------------------------------------------
        foreach ( $navigationArray  as $k => $varray )
        {
            if ( is_array( $varray ) )
            {
                foreach ( $varray as $kk => $vvarray )
                {
                    $navigation .= $vvarray;
                }
                $navigation .= '</ul></li>';
            }
            else
            {
                $navigation .= $varray;
            }
        }
        # ----------------------------------------------------------------------
        # 組合回傳
        # ----------------------------------------------------------------------
        return $navigation;
    }

    /**
    @brief      建立當前功能節點
    **/
    public function Breadcrumbs( $breadcrumbsArr = array() )
    {
        if ( ! is_array( $breadcrumbsArr ) ) return '';

        $breadcrumbsTemp = '';
        foreach( $breadcrumbsArr as $vals )
        {
            if ( ! isset( $vals['class'] ) || $vals['class'] === NULL )
            {
                $breadcrumbsTemp .= '<li>';
            }
            else
            {
                $breadcrumbsTemp .= '<li class="'.$vals['class'].'">';
            }

            if ( ! isset( $vals['href'] ) || $vals['href'] === NULL )
            {
                $breadcrumbsTemp .= $vals['text'];
            }
            else
            {
                $breadcrumbsTemp .= '<a href="'.$vals['href'].'">'.$vals['text'].'</a>';
            }
            $breadcrumbsTemp .= '</li>';
        }
        return $breadcrumbsTemp;
    }
}