<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        验证操作类，用来检查post、get、cookie的型态资料资料。
  @version      1.0.0
  @date         2015-02-27
  @since        1.0.0 -> 新增此新类别。
  @attention    注意: 此类别由系统自动初始化所以不需要手动载入。
  @remarks      $_type 参数型态
  - int ( 数字验证 )
  - float ( 浮点数 - INT 数字会通过 )
  - string ( 字串验证 )
  - int_boolean ( 数字布林验证 )
  - date ( 日期验证 )
  - time ( 24小时制时间验证 )
  - timestamp ( 时间戳记验证 )
  - mail ( 信箱验证 )
  - domain ( 网址验证 )
  - phone ( 手机验证 )
  - qq ( 腾讯QQ号 - 大陆地区 )
  - idcard_cn ( 身份证号码 - 大陆地区 )
  - china ( 是否为中文 )
  - mobilePhone_cn ( 手机和固定电话 - 大陆地区 )
  - account ( 使用者帐号验证 )
  - pass ( 使用者密码验证 )
**/

class Input
{
    /**
    @brief      正规化取代规则
    @remarks    过滤掉敏感的关键字。
    **/
    private static $preg_replace = '/]*>([\s\S]*?)<\/script[^>]*>|script|select|delete|input|update|drop|eval/i';

    /**
    @brief      预设的各类验证规则
    **/
    private $rules = array
    (
        # ----------------------------------------------------------------------
        # 数字验证
        # ----------------------------------------------------------------------
        'int' => '/^[0-9]+$/i' ,
        'int_msg' => '只允许输入0-9的数字，不包含全形。' ,

        # ----------------------------------------------------------------------
        # 浮点数 ( INT 数字会通过 )验证
        # ----------------------------------------------------------------------
        'float' => '/^\d+(\.\d+)?$/' ,
        'float_msg' => '只允许输入浮点数或数字，不包含全形。' ,

        # ----------------------------------------------------------------------
        # 字串验证
        # ----------------------------------------------------------------------
        'string' => '/^[\S\s]+$/i' ,
        'string_msg' => '只允许输入0-9的数字、a-zA-Z的英文字，不包含全形。' ,

        # ----------------------------------------------------------------------
        # 数字布林验证
        # ----------------------------------------------------------------------
        'int_boolean' => '/^(0|1)$/i' ,
        'int_boolean_msg' => '只允许输入0-1的数字，不包含全形。' ,

        # ----------------------------------------------------------------------
        # 日期验证
        # ----------------------------------------------------------------------
        'date' => '/^(((((1[26]|2[048])00)|[12]\d([2468][048]|[13579][26]|0[48]))-((((0[13578]|1[02])-(0[1-9]|[12]\d|3[01]))|((0[469]|11)-(0[1-9]|[12]\d|30)))|(02-(0[1-9]|[12]\d))))|((([12]\d([02468][1235679]|[13579][01345789]))|((1[1345789]|2[1235679])00))-((((0[13578]|1[02])-(0[1-9]|[12]\d|3[01]))|((0[469]|11)-(0[1-9]|[12]\d|30)))|(02-(0[1-9]|1\d|2[0-8])))))$/' ,
        'date_msg' => '格式不符，不是一个正确的日期格式。' ,

        # ----------------------------------------------------------------------
        # 24小时制时间验证
        # ----------------------------------------------------------------------
        'time' => '/^(2[0-3]|[0-1]?[0-9]):[0-5]?[0-9](:[0-5]?[0-9])?$/i' ,
        'time_msg' => '格式不符，不是一个正确的时间格式。' ,

        # ----------------------------------------------------------------------
        # 时间戳记验证
        # ----------------------------------------------------------------------
        'timestamp' => '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/i' ,
        'timestamp_msg' => '格式不符，不是一个正确的时间戳记格式。' ,

        # ----------------------------------------------------------------------
        # 信箱验证
        # ----------------------------------------------------------------------
        'mail' => '/^[a-z0-9_\-\.]+@[a-z0-9]+[a-z0-9\-\.]+[a-z]{2,3}$/i' ,
        'mail_msg' => '格式不符，不是一个正确的电子信箱格式。' ,

        # ----------------------------------------------------------------------
        # 网址验证
        # ----------------------------------------------------------------------
        'domain' => '/^(([a-z0-9]{1}[a-z0-9\-]*[a-z0-9]{1}\.|[a-z]{1}\.))+[a-z]{2,3}$/i' ,
        'domain_msg' => '格式不符，不是一个正确的网址格式。' ,

        # ----------------------------------------------------------------------
        # 手机验证
        # ----------------------------------------------------------------------
        'phone' => '/^[0-9]+\([0-9]+\)[0-9]+$/i' ,
        'phone_msg' => '格式不符，不是一个正确的手机号码格式。' ,

        # ----------------------------------------------------------------------
        # 腾讯QQ号 ( 大陆地区 )
        # ----------------------------------------------------------------------
        'qq' => '/^[1-9]*[1-9][0-9]*$/' ,
        'qq_msg' => '格式不符，不是一个正确的腾讯QQ号格式。' ,

        # ----------------------------------------------------------------------
        # 身份证号码 ( 大陆地区 )
        # ----------------------------------------------------------------------
        'idcard_cn' => '/\d{15}|\d{18}/' ,
        'idcard_cn_msg' => '格式不符，不是一个正确的身份证号码(CN)格式。' ,

        # ----------------------------------------------------------------------
        # 是否为中文
        # ----------------------------------------------------------------------
        'china' => '/[\u4e00-\u9fa5]/' ,
        'china_msg' => '格式不符，不是一个正确的中文格式。' ,

        # ----------------------------------------------------------------------
        # 手机和固定电话 ( 大陆地区 )
        # ----------------------------------------------------------------------
        'mobilePhone_cn' => '/(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,12}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/' ,
        'mobilePhone_cn_msg' => '格式不符，不是一个正确的手机和固定电话(CN)格式。' ,

        # ----------------------------------------------------------------------
        # 使用者帐号验证
        # ----------------------------------------------------------------------
        'account' => '/^[a-zA-Z0-9]{6,12}$/' ,
        'account_msg' => '只允许输入0-9、A-Z、a-z的英、数字，且长度需介于6-12个字元之间。' ,
    );

    /**
    @brief      存放已成功过滤后的资料
    **/
    private $filtered = array();

    /**
    @cond       建构子
    @remarks    利用建构子来产生其他验证
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 使用者密码验证
        # ----------------------------------------------------------------------
        #$this->addRule( 'pass' , '/^(?=.*\d)(?=.*[a-z]).{' . DEVIL_APP_LEASTPASS . ',' . DEVIL_APP_MAXPASS . '}$/' );
        $this->addRule( 'pass' , '/^(?!.*(.)\1{2})((?=.*[\d])(?=.*[a-z])(?=.*[A-Z])).{' . DEVIL_APP_LEASTPASS . ',' . DEVIL_APP_MAXPASS . '}$/' );
        $this->addRule( 'pass_msg' , "只允许输入最少需一个[0-9]数字、一个英文字[a-z]小写、一个英文字[A-Z]大写、且英、数字不得连续出现超过3次，且长度需介于" . DEVIL_APP_LEASTPASS . "-" . DEVIL_APP_MAXPASS . "个字元之间" );

    }
    /**
    @endcond
    **/

    /**
    @brief      增加要过滤的资料
    @param      String      资料
    @param      String      过滤分类：post、get、cookie、params
    @param      String      依照过滤清单
    **/
    private function addData( $data , $Type , $dateType )
    {
        # ----------------------------------------------------------------------
        # 字串符转大写
        # ----------------------------------------------------------------------
        switch( strtoupper( $Type ) )
        {
            case "POST"  : $importArr = $_POST;   break;
            case "GET"   : $importArr = $_GET;    break;
            case "COOKIE": $importArr = $_COOKIE; break;
            case "PARAMS": $importArr = Bootstart::$_Uri; break;
            default: $importArr = array();
        }

        # ----------------------------------------------------------------------
        # 如果是空阵列就不处理
        # ----------------------------------------------------------------------
        if ( count( $importArr ) == 0 )
        {
            return false;
        }

        if ( isset( $importArr[$data] ) )
        {
            if ( is_array( $importArr[$data] ) )
            {
                foreach( $importArr[$data] as $k => $v )
                {
                    # ----------------------------------------------------------
                    # 先做一次正规化取代
                    # ----------------------------------------------------------
                    $v = preg_replace( self::$preg_replace , '' , $v );
                    # ----------------------------------------------------------
                    # 判断是否为 UTF-8 编码
                    # ----------------------------------------------------------
                    if ( ! mb_check_encoding( $v , 'utf-8' ) )
                    {
                        return false;
                    }
                    # ----------------------------------------------------------
                    # 检查此参数是否通过指定的型态验证
                    # ----------------------------------------------------------
                    if ( $this->check( $dateType , $v ) )
                    {
                        $this->filtered[strtolower( $Type )][$data][$k] = $v;
                    }
                }
            }
            else
            {
                # --------------------------------------------------------------
                # 先做一次正规化取代
                # --------------------------------------------------------------
                $importArr[$data] = preg_replace( self::$preg_replace , '' , $importArr[$data] );
                # --------------------------------------------------------------
                # 判断是否为 UTF-8 编码
                # --------------------------------------------------------------
                if ( ! mb_check_encoding( $importArr[$data] , 'utf-8' ) )
                {
                    return false;
                }

                if ( $this->check( $dateType , $importArr[$data] ) )
                {
                    $this->filtered[strtolower( $Type )][$data] = $importArr[$data];
                }
            }
        }
        else
        {
            return false;
        }
    }

    /**
    @brief      回传正规化处理后的结果
    @param      String      $filterType
    @param      String      $value
    @return     Bool        true | false
    **/
    private function check( $filterType , $value )
    {
        if ( array_key_exists( $filterType , $this->rules ) )
        {
            return preg_match( $this->rules[$filterType] , $value );
        }
        else
        {
            Core_preDie( 'error type.' );
        }
    }

    /**
    @brief      取出指定规则的提示文字
    @param      String      $regExp 型态
    @retval     String      回传提示文字
    @remarks    此范例返回：只允许输入0-9的数字，不包含全形。
    @code{.unparsed}
    $this->Core_Inupt->getRuleMsg( 'int' );
    @endcode
    **/
    public function getRuleMsg( $regExp )
    {
        return $this->rules[$regExp . '_msg'];
    }

    /**
    @brief      自行增加规则
    @param      String      $name 规则名称
    @param      String      $regExp 正规化语法
    @remarks    此范例结果：增加对应的筛选方式。
    @code{.unparsed}
    $this->Core_Inupt->addRule( 'hello' , '/^hello$/' );
    @endcode
    **/
    public function addRule( $name , $regExp )
    {
        $this->rules[$name] = $regExp;
    }

    /**
    @brief      取出 GET 的指定值
    @param      String      $n Index索引
    @param      String      $_type 型态
    @retval     Bollen      Mix | Fasle
    @remarks    此范例结果：判断get到的资料是否符合字串。
    @code{.unparsed}
    $this->Core_Inupt->get( 'name' , 'string' );
    @endcode
    **/
    public function get( $n , $_type )
    {
        if ( ! isset( $n ) && ! isset( $_type ) )
        {
            return false;
        }
        else
        {
            if ( $this->addData( $n , 'get' , $_type ) === false )
            {
                return false;
            }

            if ( array_key_exists( 'get' , $this->filtered ) )
            {
                if ( array_key_exists( $n , $this->filtered['get'] ) )
                {
                    return $this->filtered['get'][$n];
                }
                else
                {
                    return false;
                }
            }
        }
        return false;
    }

    /**
    @brief      取出 POST 的指定值
    @param      String      $n Index索引
    @param      String      $_type 型态
    @retval     Bollen      Mix | Fasle
    @remarks    此范例结果：判断post到的资料是否符合字串。
    @code{.unparsed}
    $this->Core_Inupt->post( 'name' , 'string' );
    @endcode
    **/
    public function post( $n , $_type )
    {
        if ( ! isset( $n ) && ! isset( $_type ) )
        {
            return false;
        }
        else
        {
            if ( $this->addData( $n , 'post' , $_type ) === false )
            {
                return false;
            }

            if ( array_key_exists( 'post' , $this->filtered ) )
            {
                if ( array_key_exists( $n , $this->filtered['post'] ) )
                {
                    return $this->filtered['post'][$n];
                }
                else
                {
                    return false;
                }
            }
        }
        return false;
    }

    /**
    @brief      取出 COOKIE 的指定值
    @param      String      $n Index索引
    @param      String      $_type 型态
    @retval     Bollen      Mix | Fasle
    @remarks    此范例结果：判断cookie到的资料是否符合字串。
    @code{.unparsed}
    $this->Core_Inupt->cookie( 'name' , 'string' );
    @endcode
    **/
    public function cookie( $n , $_type )
    {
        if ( ! isset( $n ) && ! isset( $_type ) )
        {
            return false;
        }
        else
        {
            if ( $this->addData( $n , 'cookie' , $_type ) === false )
            {
                return false;
            }

            if ( array_key_exists( 'cookie' , $this->filtered ) )
            {
                if ( array_key_exists( $n , $this->filtered['cookie'] ) )
                {
                    return $this->filtered['cookie'][$n];
                }
                else
                {
                    return false;
                }
            }
        }
        return false;
    }

    /**
    @brief      取出 Params 的指定值
    @param      Int         $n Index索引
    @param      String      $_type 型态
    @retval     Bollen      Mix | Fasle
    @remarks    此范例结果：判断params到的资料是否符合字串。
    @code{.unparsed}
    $this->Core_Inupt->params( 'name' , 'string' );
    @endcode
    **/
    public function params( $n , $_type )
    {
        # ----------------------------------------------------------------------
        # 判斷指定的索引有無存在 params 的全域變數中
        # ----------------------------------------------------------------------
        if ( $this->addData( $n , 'params' , $_type ) === false )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # 判斷過濾的資料中有無 params 索引的資料陣列存在
        # ----------------------------------------------------------------------
        if ( array_key_exists( 'params' , $this->filtered ) )
        {
            # ------------------------------------------------------------------
            # 有存在的話就取出過濾的資料
            # ------------------------------------------------------------------
            if ( array_key_exists( $n , $this->filtered['params'] ) )
            {
                return $this->filtered['params'][$n];
            }
        }
        # ----------------------------------------------------------------------
        # 回傳結果
        # ----------------------------------------------------------------------
        return false;
    }

 /**
    @brief      取出 驗證後的指定值
    @param      Int         $_variable 索引
    @param      String      $_type 型态
    @retval     Bollen      Mix | Fasle
    @remarks    此范例结果：判断變數的资料是否符合字串型態。
    @code{.unparsed}
    $this->Core_Inupt->variable( 'name' , 'string' );
    @endcode
    **/
    public function variable( $_variable , $_type = 'string' )
    {
        # ----------------------------------------------------------------------
        # 回传正规化处理后的结果
        # ----------------------------------------------------------------------
        $_data = self::check( $_type , $_variable );
        # ----------------------------------------------------------------------
        # 如果不符合型態就回傳false
        # ----------------------------------------------------------------------
        return ( $_data ) ? $_variable : false;
    }
}