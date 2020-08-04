<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        ★★★★★ 核心 Debug ( 除錯器類別-保留用 )
  @version      1.0.0
  @date         2015-03-01 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

final class Debug
{
    /**
    @brief string
    **/
    private static $_startTime;

    /**
    @brief string
    **/
    private static $_endTime;

    /**
    @brief array
    **/
    private static $_arrGeneral;

    /**
    @brief array
    **/
    private static $_arrParams;

    /**
    @brief array
    **/
    private static $_arrWarnings;

    /**
    @brief array
    **/
    private static $_arrLogs;

    /**
    @brief array
    **/
    private static $_arrQueries;

    /**
    @brief array
    **/
    private static $_arrAjax;

    /**
    @cond      建構子
    @remarks    利用建構子產生起始時間、判斷是否為開發模式。
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 如果不是 "開發" 模式，就回傳 FALSE
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT != 'development' ) return false;

        # ----------------------------------------------------------------------
        # 設定起始的時間
        # ----------------------------------------------------------------------
        self::$_startTime = self::getFormattedMicrotime();
    }
    /**
    @endcond
    **/

    /**
    @brief      將要顯示的訊息資料處理到除錯器中
    @param      String      $type 訊息類別
    @param      String      $key index值
    @param      String | Array     $val 要存入的識別資料
    **/
    public function addMessage( $type = 'params' , $key = '' , $val = '' )
    {
        # ----------------------------------------------------------------------
        # 如果不是 "開發" 模式，就回傳 FALSE
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT != 'development' ) return false;

        # ----------------------------------------------------------------------
        # 依照不同的 "類別" 處理
        # ----------------------------------------------------------------------
        switch( $type )
        {
            case "general":
                self::$_arrGeneral[$key] = $val;
                break;
            case "params":
                self::$_arrParams[$key] = $val;
                break;
            case "log":
                self::$_arrLogs[$key] = $val;
                break;
            case "warnings":
                self::$_arrWarnings[$key] = $val;
                break;
            case "queries":
                self::$_arrQueries[$key] = $val;
                break;
            case "ajax":
                self::$_arrAjax[$key] = $val;
                break;
        }
    }

    /**
    @brief      將要顯示的訊息資料處理到除錯器中  Get message from the stack
    @param      String      $type 訊息類別
    @param      String      $key $key index值
    @return     String
    **/
    public function getMessage( $type = 'params' , $key = '' )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $output = '';

        # ----------------------------------------------------------------------
        # 如果是 "log" 當沒有設定時，就回傳 空值
        # ----------------------------------------------------------------------
        if ( $type == 'warnings' ) $output = ( isset( self::$_arrWarnings[$key] ) ? self::$_arrWarnings[$key] : '' );

        return $output;
    }

    /**
    @brief      顯示除錯器
    @param      Bool        $_ajax 是否為 ajax
    **/
    public function displayInfo( $_ajax = false )
    {
        # ----------------------------------------------------------------------
        # 如果不是開發模式，就回傳 false
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT != 'development' ) return false;
        # ----------------------------------------------------------------------
        # 設定結束時間
        # ----------------------------------------------------------------------
        self::$_endTime = self::GetFormattedMicrotime();
        # ----------------------------------------------------------------------
        # 如果是使用 ajax
        # ----------------------------------------------------------------------
        if ( $_ajax )
        {
            $TempdeBug = array
            (
                'phpdebugbar-time' => round( (float)self::$_endTime - (float)self::$_startTime , 3 ) . ' sec' ,
                'phpdebugbar-gear' => Core_Memory() ,
                'General' => array
                (
                    'cookie_pre' => '<dt>$_COOKIE  </dt><dd class="shoutpretty">'. $this->ExportTag( $_COOKIE ) . '</dd>' ,
                    'post_pre' => '<dt>$_POST  </dt><dd class="shoutpretty">'. $this->ExportTag( $_POST ) . '</dd>' ,
                    'get_pre' => '<dt>$_GET </dt><dd class="shoutpretty">'. $this->ExportTag( $_GET ) . '</dd>'
                ) ,
                'Params' => array
                (
                    'Params_Count' => count( self::$_arrParams ) ,
                    'contentParams' => $this->ExportTag( self::$_arrParams , TRUE )
                ) ,
                'Warnings_Count' => array
                (
                    'Warnings_Count' => count( self::$_arrWarnings ) ,
                    #'contentWarnings_pre' => var_export( ( count( self::$_arrWarnings ) > 0 ) ? array_map( 'strip_tags', self::$_arrWarnings ) : NULL , true )
                    'contentWarnings' => ( count( self::$_arrWarnings ) > 0 ) ? $this->ExportTag( array_reverse( self::$_arrWarnings ) , TRUE ) : ''
                ) ,
                'Logs_Count' => array
                (
                    'Logs_Count' => count( self::$_arrLogs ) ,
                    #'contentLogs_pre' => var_export( ( count( self::$_arrLogs ) > 0 ) ? array_map( 'strip_tags', self::$_arrLogs ) : NULL , true )
                    'contentLogs' => $this->ExportTag( array_reverse( self::$_arrLogs ) , TRUE )
                ) ,
                'Ajax_Count' => array
                (
                    'Ajax_Count' => count( self::$_arrAjax ) ,
                    'contentAjax' => var_export( ( count( self::$_arrAjax ) > 0 ) ? array_map( 'strip_tags', self::$_arrAjax ) : NULL , true )
                ) ,
                'SQL_Count' => array
                (
                    'Queries_Count' => count( self::$_arrQueries ) ,
                    #'contentQueries_pre' => var_export( ( count( self::$_arrQueries ) > 0 ) ? array_map( 'strip_tags', self::$_arrQueries ) : NULL , true )
                    'contentQueries' => $this->ExportTag( array_reverse( self::$_arrQueries ) , TRUE )
                )

            );
            return $TempdeBug;
        }
        else
        {
            echo "<div id=\"debug-panel\">
                    <div class=\"fieldset\">
                    <div class=\"legend\" id=\"debug-panel-legend\" >
                        <span class=\"phpdebugbar-indicator-last fRight\" ><span class=\"phpdebugbar-close hide\" id=\"debugArrowCollapse\" >&nbsp;</span></span>
                        <span class=\"phpdebugbar-indicator fRight\" title=\"PHP Version\"><span class=\"phpdebugbar-php\">" . phpversion() . "</span></span>
                        <span class=\"phpdebugbar-indicator fRight\" title=\"Framework MVC Version\"><span class=\"phpdebugbar-tools\" >v" . Bootstart::Framework_v() . "</span></span>
                        <span class=\"phpdebugbar-indicator fRight\" title=\"Memory Usage\" ><span class=\"phpdebugbar-gear\">" . Core_Memory() . "</span></span>
                        <span class=\"phpdebugbar-indicator fRight\" title=\"AJAX Complete Total Running Time\" ><span class=\"phpdebugbar-jstime\"></span></span>
                        <span class=\"phpdebugbar-indicator fRight\" title=\"Total Running Time\" ><span class=\"phpdebugbar-time\">" . round( (float)self::$_endTime - (float)self::$_startTime , 3 ) . " sec</span></span>
                        <b class=\"bold\" style=\"color:#555\">Debuger</b>

                        <span>
                            &nbsp;<a href=\"javascript:\" class=\"debugItem on\" item=\"tabGeneral\" >基礎</a> &nbsp;&nbsp;
                            &nbsp;<a href=\"javascript:\" class=\"debugItem\" item=\"tabAjax\" >封包</a> &nbsp;&nbsp;
                            &nbsp;<a href=\"javascript:\" class=\"debugItem\" item=\"tabParams\" >參數(<span id=\"Params_Count\">" . count( self::$_arrParams ) . "</span>)</a> &nbsp;&nbsp;
                            &nbsp;<a href=\"javascript:\" class=\"debugItem\" item=\"tabLogs\" >核心(<span id=\"Logs_Count\">" . count( self::$_arrLogs ) . "</span>)</a> &nbsp;&nbsp;
                            &nbsp;<a href=\"javascript:\" class=\"debugItem\" item=\"tabWarnings\" >錯誤(<span id=\"Warnings_Count\">" . count( self::$_arrWarnings ) . "</span>)</a> &nbsp;&nbsp;
                            &nbsp;<a href=\"javascript:\" class=\"debugItem\" item=\"tabQueries\" >SQL(<span id=\"Queries_Count\">" . count( self::$_arrQueries ) . "</span>)</a>
                        </span>
                    </div>
                    <div id=\"contentGeneral\" class=\"debugItemcontent hide\" style=\"height:220px;overflow-y:auto;\">
                        <dl id=\"get_pre\"><dt>&amp;_GET  </dt><dd class=\"shoutpretty\">{$this->ExportTag( $_GET )}</dd></dl>
                        <dl id=\"post_pre\"><dt>&amp;_POST  </dt><dd class=\"shoutpretty\">{$this->ExportTag( $_POST )}</dd></dl>
                        <dl id=\"cookie_pre\"><dt>&amp;_COOKIE  </dt><dd class=\"shoutpretty\">{$this->ExportTag( $_COOKIE )}</dd></dl>
                    </div>
                    <div id=\"contentParams\" class=\"debugItemcontent hide\" style=\"height:220px;overflow-y:auto;\">";
                    echo $this->ExportTag( self::$_arrParams , TRUE );
                    echo '</div>
                    <div id="contentWarnings" class="debugItemcontent hide" style="height:220px;overflow-y:auto;">';
                    echo ( count( self::$_arrWarnings ) > 0 ) ? $this->ExportTag( array_reverse( self::$_arrWarnings ) , TRUE ) : '<dl id="contentWarnings_pre"></dl>';
                    #echo $this->ExportTag( self::$_arrWarnings , 'contentWarnings_pre' );
                    echo '</div>
                    <div id="contentLogs" class="debugItemcontent hide" style="height:220px;overflow-y:auto;">';
                    echo $this->ExportTag( array_reverse( self::$_arrLogs ) , TRUE );
                    echo '</div>
                    <div id="contentAjax" class="debugItemcontent hide" style="height:220px;overflow-y:auto;">
                    <ul class="phpdebugbar-widgets-timeline">
                      <li class="measure dethead" >
                        <span class="label w5">Method</span>
                        <span class="label w5">Status</span>
                        <span class="label w20">Type</span>
                        <span class="label w5">Size</span>
                        <span class="label w15">Time</span>
                        <span class="label">Path<span class="phpdebugbar-cancel">&nbsp;</span></span>
                      </li>
                    </ul>';
                    #echo '<pre class="linebr" id="contentAjax_pre">';
                    if ( count( self::$_arrAjax ) > 0 )
                    {
              				  print_r( self::$_arrAjax );
              			}
                    #echo '</pre>';
                    echo '<br></div>
                    <div id="contentQueries" class="debugItemcontent hide" style="height:220px;overflow-y:auto;">';
                    echo $this->ExportTag( array_reverse( self::$_arrQueries ) , TRUE );
                    echo '</div>
                    </div>
                  </div>';
            echo '<script type="text/javascript">
                	var yHeight = $( window ).height();
                  $( "#layout" ).css( "margin-bottom" , "55px" );
                	$( ".floor" ).css({ bottom: 27 + "px" });
                  $( "#debug-panel .debugItemcontent" ).css({ height: ( $( window ).height() * 0.35 ) + "px" });
                  $(function() {
                      $("body").on( "click", "#debugArrowCollapse" , function() {
                    		$(this).addClass( "hide" );
                    		$("#debugArrowExpand").removeClass( "hide" );
                    		$( ".debugItemcontent" ).addClass( "hide" );
                    	});
                    	$("body").on( "click", ".debugItemcontent dd" , function() {
                    		 $(this).toggleClass( "pretty , shoutpretty" );
                    	});
                    	//
                    	$("body").on( "click", ".debugItem" , function() {
                    		$("#debugArrowCollapse").removeClass( "hide" );
                    		$( ".debugItemcontent" ).addClass( "hide" );
                    		$( ".debugItem" ).removeClass( "on" );
                    		$(this).addClass( "on" );
                    		var itemcontent = $(this).attr( "item" ).substr( 3 );
                    		$( "#content" + itemcontent ).removeClass( "hide" );
                    	});
                  		$( ".debugItemcontent dl:odd").addClass( "odd" );
                  		$( ".debugItemcontent dl:even").addClass( "even" );
                  });
                  </script>';
        }
    }

    /**
    @brief      計算時間用
    @return     Float
    **/
    private static function getFormattedMicrotime()
    {
        # ----------------------------------------------------------------------
        # 如果不是 "開發" 模式，就回傳 FALSE
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT != 'development' ) return false;

        # ----------------------------------------------------------------------
        # 切割毫秒、秒
        # ----------------------------------------------------------------------
        list ( $usec , $sec ) = explode( ' ' , microtime() );

        # ----------------------------------------------------------------------
        # 回傳兩者相加值
        # ----------------------------------------------------------------------
        return ( (float)$usec + (float)$sec );
    }

    /**
    @brief      展開紀錄的訊息內容，可為陣列
    @param      Array       $_array 陣列參數
    @param      Array       $_dom 另外處理tag
    @return     String
    **/
    private function ExportTag( $_array = array() , $_dom = FALSE )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $relaceStr = '';

        # ----------------------------------------------------------------------
        # 如果傳入的資料為空，就回傳空值
        # ----------------------------------------------------------------------
        if ( ! $_array )
        {
            return "Array \n(\n)\n";
        }
        # ----------------------------------------------------------------------
        # 如果有資料就在這邊組合處理
        # ----------------------------------------------------------------------
        else
        {
            if ( $_dom )
            {
                # --------------------------------------------------------------
                # 開始組合 dl 、 dt 、 dd 的內容
                # --------------------------------------------------------------
                foreach( $_array as $k => $v )
                {
                    # ----------------------------------------------------------
                    # 如果是陣列，就另外組合陣列中的資料
                    # ----------------------------------------------------------
                    if ( is_array( $v ) )
                    {
                        # ------------------------------------------------------
                        # 這邊判斷，若字元長度大於 20 ，就利用 CSS 增加寬度
                        # ------------------------------------------------------
                        if ( mb_strlen( $k , "utf-8" ) > 20 )
                        {
                            $relaceStr .= "<dl><dt class=\"lag\">{$k}  </dt><dd class=\"lag shoutpretty\">";
                        }
                        else
                        {
                            $relaceStr .= "<dl><dt>{$k}  </dt><dd class=\"shoutpretty\">";
                        }
                        $relaceStr .= $this->ExportTag( $v );
                        $relaceStr .= "</dd></dl>";
                    }
                    else
                    {
                        # ------------------------------------------------------
                        # 這邊判斷，若字元長度大於 20 ，就利用 CSS 增加寬度
                        # ------------------------------------------------------
                        if ( mb_strlen( $k , "utf-8" ) > 20 )
                        {
                            $relaceStr .= "<dl id=\"{$_dom}\"><dt class=\"lag\">{$k}  </dt><dd class=\"lag shoutpretty\">{$v}</dd></dl>";
                        }
                        else
                        {
                            $relaceStr .= "<dl id=\"{$_dom}\"><dt>{$k}  </dt><dd class=\"shoutpretty\">{$v}</dd></dl>";
                        }
                    }
                }
            }
            else
            {
                # --------------------------------------------------------------
                # 開始組合 dd 的內容
                # --------------------------------------------------------------
                $relaceStr = "Array \n(\n";
                foreach ( $_array as $k => $v )
                {
                    # ----------------------------------------------------------
                    # 若是 "物件" ，就顯示 "文字"
                    # ----------------------------------------------------------
                    if ( is_object( $v ) )
                    {
                        $relaceStr .= "      [{$k}] =&gt; Is Operational.\n";
                    }
                    else
                    {
                        $relaceStr .= "      [{$k}] =&gt; {$v}\n";
                    }
                }
                $relaceStr .= ")\n";
            }
        }
        return $relaceStr;
    }

    /**
    @brief      跟踪程序的执行路径，主要用于程序调试
    **/
    public function dumpTrace()
    {
        $debug = debug_backtrace();
        $lines = '';
        $index = 0;
        for ($i = 0; $i < count($debug); $i ++)
        {
            if ($i == 0)
            {
                continue;
            }
            $file = $debug[$i];
            if (! isset($file['file']))
            {
                $file['file'] = 'eval';
            }
            if (! isset($file['line']))
            {
                $file['line'] = null;
            }
            $line = "#{$index} {$file['file']}({$file['line']}): ";
            if (isset($file['class']))
            {
                $line .= "{$file['class']}{$file['type']}";
            }
            $line .= "{$file['function']}(";
            if (isset($file['args']) && count($file['args']))
            {
                foreach ($file['args'] as $arg)
                {
                    $line .= gettype($arg) . ', ';
                }
                $line = substr($line, 0, - 2);
            }
            $line .= ')';
            $lines .= $line . "\n";
            $index ++;
        }

        $lines .= "#{$index} {main}\n";

        if (ini_get('html_errors'))
        {
            echo nl2br(str_replace(' ', '&nbsp;', $lines));
        }
        else
        {
            echo $lines;
        }
    }

    /**
    @brief      格式化输出
    @param      String      $var
    @param      Boolean     $echo
    @param      String      $label
    @param      Boolean     $strict
    @retval     Void
    @note
    - 1.浏览器友好的变量输出，var支持任何变量，echo表示是否需要输出，如果为否，则返回要显示的字符串。
    - 2.Strict表示是否输出详细信息，如果为否，使用print_r输出，如果为是，使用var_dump输出。
    - 3.Dump函数还支持xdebug扩展
    **/
    public static function dump( $var , $label = null , $strict = true , $echo = true )
    {
        dump( $var , $label , $strict , $echo );
    }
}