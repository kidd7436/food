<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        常用功能函式类。
  @version      1.0.0
  @date         2015-03-06
  @since        1.0.0 -> 新增此新类别。
  @attention    注意: 此类别由系统自动初始化所以不需要手动载入。
**/

class Widget
{
    /**
    @brief      生成验证码图片 ( 一般数字 )
    @param      String      $_EntryStr 加密后的字串
    @remarks    验证码||时间戳记||扰乱字串
    @code{.unparsed}
    $this->Core_Widget->captchaNum( 验证码||时间戳记||扰乱字串 );
    @endcode
    **/
    public function captchaNum( $_EntryStr )
    {
        # ----------------------------------------------------------------------
        # 检查是否有传参数
        # ----------------------------------------------------------------------
        if ( ! isset( $_EntryStr ) )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 验证码由传入的参数解密后取出使用
        # ----------------------------------------------------------------------
        $_EntryStrArr = explode( '||' , Bootstart::$_lib['Core_EncryptDecryt']->decrypt( $_EntryStr ) );
        # ----------------------------------------------------------------------
        # 如果解密后切割不是三个阵列，就跳出
        # ----------------------------------------------------------------------
        if ( count( $_EntryStrArr ) != 3 )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 有效时间 ( 三分钟 )
        # ----------------------------------------------------------------------
        if ( ( time() - $_EntryStrArr[1] ) > DEVIL_APP_CAPTCHAEXPIRE )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 验证码
        # ----------------------------------------------------------------------
        $authnum = $_EntryStrArr[0];

        Header( "Content-type: image/PNG" );
        srand( (double)microtime() * 1000000 );
        $im = imagecreate( 85 , 50 );
        $black = ImageColorAllocate( $im , 0 , 0 , 0 );
        $white = ImageColorAllocate( $im , 255 , 255 , 255 );
        $gray  = ImageColorAllocate( $im , 200 , 200 , 200 );
        imagefill( $im , 91 , 30 , $gray );
        # ----------------------------------------------------------------------
        # 将四位元整数验证码绘入图片
        # ----------------------------------------------------------------------
        imagestring( $im , 15 , 15 , 15 , $authnum , $white );
        # ----------------------------------------------------------------------
        # 加入干扰象素
        # ----------------------------------------------------------------------
        // for ( $i = 0; $i < 150; $i++ )
        // {
        //     $randcolor = ImageColorallocate( $im , rand( 200 , 255 ) , rand( 200 , 255 ) , rand( 200 , 255 ) );
        //     imagesetpixel( $im , rand() % 90 , rand() % 30 , $randcolor );
        // }
        ImagePNG( $im );
        ImageDestroy( $im );
        exit;
    }

    /**
    @brief      生成验证码图片 ( 扭曲版 )
    @param      String      $_EntryStr 加密后的字串
    @remarks    验证码||时间戳记||扰乱字串
    @code{.unparsed}
    $this->Core_Widget->captchaDis( 验证码||时间戳记||扰乱字串 );
    @endcode
    **/
    public function captchaDis( $_EntryStr )
    {
        # ----------------------------------------------------------------------
        # 检查是否有传参数
        # ----------------------------------------------------------------------
        if ( ! isset( $_EntryStr ) )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 验证码由传入的参数解密后取出使用
        # ----------------------------------------------------------------------
        $_EntryStrArr = explode( '||' , Bootstart::$_lib['Core_EncryptDecryt']->decrypt( $_EntryStr ) );
        # ----------------------------------------------------------------------
        # 如果解密后切割不是三个阵列，就跳出
        # ----------------------------------------------------------------------
        if ( count( $_EntryStrArr ) != 3 )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 有效时间 ( 三分钟 )
        # ----------------------------------------------------------------------
        if ( ( time() - $_EntryStrArr[1] ) > DEVIL_APP_CAPTCHAEXPIRE )
        {
            exit;
        }
        # ----------------------------------------------------------------------
        # 验证码
        # ----------------------------------------------------------------------
        $text = $_EntryStrArr[0];
        # ----------------------------------------------------------------------
        # 设定验证码图片的宽度、高度
        # ----------------------------------------------------------------------
        $im_x = 91;
        $im_y = 30;
        # ----------------------------------------------------------------------
        # 产生一个预设指定大小的图像 ( 黑色 )
        # ----------------------------------------------------------------------
        $im = imagecreatetruecolor( $im_x , $im_y );
        # ----------------------------------------------------------------------
        # 将图像的文字颜色设置随机 RGB 颜色
        # ----------------------------------------------------------------------
        $text_c = ImageColorAllocate( $im , mt_rand( 0 , 100 ) , mt_rand( 0 , 100 ) , mt_rand( 0 , 100 ) );
        $line_c = ImageColorAllocate( $im , mt_rand( 0 , 100 ) , mt_rand( 0 , 100 ) , mt_rand( 0 , 100 ) );
        # ----------------------------------------------------------------------
        # 将图像的文字颜色设置随机 RGB 颜色
        # ----------------------------------------------------------------------
        $buttum_c = ImageColorAllocate( $im , mt_rand( 100 , 255 ) , mt_rand( 100 , 255 ) , mt_rand( 100 , 255 ) );
        # ----------------------------------------------------------------------
        # 先涂上背景颜色
        # ----------------------------------------------------------------------
        imagefill( $im , 16 , 13 , $buttum_c );
        # ----------------------------------------------------------------------
        # 使用字形
        # ----------------------------------------------------------------------
        $font = FONTS_PATH . '/t1.ttf';
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $tmp = substr( $text , $i , 1 );
            $array = array( -1 , 1 );
            $p = array_rand( $array );
            $an = $array[$p] * mt_rand( 1 , 10 );
            $size = 14;
            imagettftext( $im, $size , $an , 1 + $i * $size , 23 , $text_c , $font , $tmp );
        }

        $distortion_im = imagecreatetruecolor ( $im_x, $im_y );
        imagefill( $distortion_im , 16 , 13 , $buttum_c );

        for ( $i = 0; $i < $im_x; $i++ )
        {
            for ( $j = 0; $j < $im_y; $j++ )
            {
                $rgb = imagecolorat( $im , $i , $j );
                if( (int)( $i + 20 + sin( $j / $im_y * 2 * M_PI ) * 10 ) <= imagesx( $distortion_im )&& (int)( $i + 20 + sin( $j / $im_y * 2 * M_PI ) * 10 ) >=0 )
                {
                    imagesetpixel( $distortion_im , (int)( $i + 10 + sin( $j / $im_y * 2 * M_PI - M_PI * 0.1 ) * 4 ) , $j , $rgb );
                }
            }
        }
        # ----------------------------------------------------------------------
        # 干扰像素
        # ----------------------------------------------------------------------
        $count = 50;
        for ( $i=0; $i < $count; $i++ )
        {
            $randcolor = ImageColorallocate( $distortion_im , mt_rand( 0 , 255 ) , mt_rand( 0 , 255 ) , mt_rand( 0 , 255 ) );
            imagesetpixel( $distortion_im , mt_rand() % $im_x , mt_rand() % $im_y , $randcolor );
        }

        $rand = mt_rand( 5 ,10 );
        $rand1 = mt_rand( 15 ,17 );
        $rand2 = mt_rand( 5 , 10 );
        for ( $yy = $rand; $yy <=+$rand + 1; $yy++ )
        {
            for ( $px = -80; $px <= 80; $px = $px + 0.1 )
            {
                $x = $px / $rand1;
                if ( $x != 0 )
                {
                    $y = sin( $x );
                }
                $py = $y * $rand2;
                imagesetpixel( $distortion_im , $px + 80 , $py + $yy , $text_c );
            }
        }

        Header( "Content-type: image/PNG" );
        ImagePNG( $distortion_im );
        ImageDestroy( $distortion_im );
        ImageDestroy( $im );
        exit;
    }


    /**
    @brief      XOR encrypts a given string with a given key phrase.
    @param      String      $InputString Input string
    @param      String      $KeyPhrase Key phrase
    @return     String      Encrypted string
    **/
    private function xorEncryption( $InputString , $KeyPhrase )
    {
        $KeyPhraseLength = strlen( $KeyPhrase );
        # ------------------------------------------------------------------
        # Loop trough input string
        # ------------------------------------------------------------------
        for ( $i = 0; $i < strlen( $InputString ); $i++ )
        {
            # --------------------------------------------------------------
            # Get key phrase character position
            # --------------------------------------------------------------
            $rPos = $i % $KeyPhraseLength;
            # --------------------------------------------------------------
            # Magic happens here:
            # --------------------------------------------------------------
            $r = ord( $InputString[$i] ) ^ ord( $KeyPhrase[$rPos] );
            # --------------------------------------------------------------
            # Replace characters
            # --------------------------------------------------------------
            $InputString[$i] = chr( $r );
        }
        return $InputString;
    }

    /**
    @brief      XOREncrypt
    @param      String      $InputString Input string
    @param      String      $KeyPhrase Key phrase
    @retval     String
    **/
    public function xorEncrypt( $InputString , $KeyPhrase )
    {
        $InputString = self::xorEncryption( $InputString , $KeyPhrase );
        $InputString = base64_encode( $InputString );
        return $InputString;
    }

    /**
    @brief      XOREncrypt
    @param      String      $InputString Input string
    @param      String      $KeyPhrase Key phrase
    @retval     String
    **/
    public function xorDecrypt( $InputString , $KeyPhrase )
    {
        $InputString = base64_decode( $InputString );
        $InputString = self::xorEncryption( $InputString , $KeyPhrase );
        return $InputString;
    }

    /**
    @brief      产生一个随机数
    @param      int         $min 最小
    @param      int         $max 最大
    @retval     integer
    **/
    public function randNum( $min = null , $max = null )
    {
        # ----------------------------------------------------------------------
        # 处理乱数种子
        # ----------------------------------------------------------------------
        mt_srand( (double)microtime() * 1000000 );
        # ----------------------------------------------------------------------
        # 随机产生
        # ----------------------------------------------------------------------
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
    @brief      产生随机字串，可用来自动生成密码 默认长度6位 字母和数字溷合
    @param      string      $len 长度
    @param      string      $type 0大小写字母，1数字，2大写字母，3小写字母，4中文,5大小写数字
    @param      string      $addChars 额外字符
    @retval     string
    @remarks    使用范例如下：
    @code{.unparsed}
    $this->Core_Widget->randString();
    @endcode
    **/
    public function randString( $len = 5 , $type = 1 , $addChars = '' )
    {
        # ----------------------------------------------------------------------
        # 初始化变数
        # ----------------------------------------------------------------------
        $str = '';
        # ----------------------------------------------------------------------
        # 依照类型产生随机字串
        # ----------------------------------------------------------------------
        switch ( $type )
        {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            default:
            case 1:
                $chars = str_repeat( '0123456789' , 3 );
                break;
            case 2:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            case 4:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培着河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官溷纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻桉刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔勐诉刷狠忽灾闹乔唐漏闻沉熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智澹允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
                break;
            case 5:
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
                break;
            case 6:
                # --------------------------------------------------------------
                # 默认去掉了容易溷淆的字符oOLl和数字01，要添加请使用addChars参数
                # --------------------------------------------------------------
                $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
                break;
        }
        if ( $len > 10 )
        {
            # ------------------------------------------------------------------
            # 位数过长重复字符串一定次数
            # ------------------------------------------------------------------
            $chars = ( $type == 1 ) ? str_repeat( $chars , $len ) : str_repeat( $chars , 5 );
        }
        if ( $type != 4 )
        {
            $chars = str_shuffle( $chars );
            $str = substr( $chars , 0 , $len );
        }

        return $str;
    }

    /**
    @brief      返回永远唯一32位md5的随机数
    @retval     String
    @remarks    使用范例如下：
    @code{.unparsed}
    $this->Core_Widget->md5Rand();
    @endcode
    **/
    public function md5Rand()
    {
        srand( (double)microtime() * 1000000 );
        return md5( uniqid( time() . rand() ) );
    }

    /**
    @brief      安全base64_encode
    @remarks    替换掉+ / = 字符，这样不用urldecode了
    @param      String      $string
    @retval     String
    @remarks    使用范例如下：
    @code{.unparsed}
    $this->Core_Widget->safe_b64encode( '要加密的内容' );
    @endcode
    **/
    public function safe_b64encode( $string )
    {
        $data = base64_encode( $string );
        $data = str_replace( array( '+' , '/' , '=' ) , array( '-' , '_' , '' ) , $data );
        return $data;
    }

    /**
    @brief      安全的base64_encode
    @param      String      $string
    @retval     String
    @remarks    使用范例如下：
    @code{.unparsed}
    $this->Core_Widget->safe_b64decode( '要解密的内容' );
    @endcode
    **/
    public function safe_b64decode( $string )
    {
        $data = str_replace( array( '-' , '_' ) , array( '+' , '/' ) , $string );
        $mod4 = strlen( $data ) % 4;
        if ( $mod4 )
        {
            $data .= substr( '====' , $mod4 );
        }
        return base64_decode( $data );
    }

    /**
    @brief      档桉修改独佔模式
    @param      String      $filename
    @remarks    使用范例如下：
    @code{.unparsed}
    $lock = $this->Core_Widget->only_lock( 'data.counter' );

    $filedata = @file( 'data.counter' );

    $count = isset( $filedata[0] ) ? $filedata[0] + 1 : 1;
    file_put_contents( 'data.counter' , $count );

    $this->Core_Widget->only_unlock( 'data.counter' );

    echo '<pre>';
    readfile( 'data.counter' );
    @endcode
    **/
    public function only_lock( $filename )
    {
        #------------------------------------
        # 解除使用者终止程式的权力，直到我们完成锁定为止。
        #------------------------------------
        ignore_user_abort( true );
        #------------------------------------
        # 开始计数，我们只想尝试取得几次锁定
        #------------------------------------
        $counter = 0;
        #------------------------------------
        # 一直到我们取得锁定为止，否则停止秒钟。
        #------------------------------------
        do
        {
            #------------------------------------
            # 停止秒数为计数器的平方，每一次都越来越长。
            #------------------------------------
            sleep( $counter * $counter );
            #------------------------------------
            # 建立目录
            #------------------------------------
            $succsee = @mkdir( "{$filename} . dirlock" );
        }
        while( ! ( $succsee ) && ( $counter++ < 10 ) );
        #------------------------------------
        # 如果计数器到达11，就不在尝试取得锁定
        #------------------------------------
        if ( $counter == 11 )
        {
            die( 'Error: Could not get exclusive lock!!' );
        }
    }

    /**
    @brief      解除档桉修改独佔模式
    @param      String      $filename
    @remarks    使用范例如下：
    @code{.unparsed}
    $this->Core_Widget->only_unlock( 'data.counter' );
    @endcode
    **/
    public function only_unlock( $filename )
    {
        #------------------------------------
        # 删除目录
        #------------------------------------
        if ( ! ( rmdir( "{$filename} . dirlock" ) ) )
        {
            die( 'Error: Could not get exclusive lock!!' );
        }
        #------------------------------------
        # 解除使用者终止程式的限制。
        #------------------------------------
        ignore_user_abort( false );
    }

    /**
    @brief      代码加亮
    @param      String      $str 要高亮显示的字符串 或者 文件名
    @param      Bool        $show 是否输出
    @return     String
    **/
    public function highlight_code( $str , $show = false )
    {
        if ( file_exists( $str ) )
        {
            $str = file_get_contents( $str );
        }
        $str  =  stripslashes( trim( $str ) );

        $str = str_replace( array( '&lt;' , '&gt;' ) , array( '<' , '>' ) , $str );
        $str = str_replace( array( '&lt;?php' , '?&gt;' , '\\' ) , array( 'phptagopen' , 'phptagclose' , 'backslashtmp' ) , $str );
        # ----------------------------------------------------------------------
        # <?
        # ----------------------------------------------------------------------
        $str = '<?php //tempstart' . "\n" . $str . '//tempend ?>';
        # ----------------------------------------------------------------------
        # All the magic happens here, baby!
        # ----------------------------------------------------------------------
        $str = highlight_string( $str , true );

        if ( abs( phpversion() ) < 5 )
        {
            $str = str_replace(array( '<font ' , '</font>' ) , array( '<span ' , '</span>' ) , $str );
            $str = preg_replace( '#color="(.*?)"#' , 'style="color: \\1"' , $str );
        }
        # ----------------------------------------------------------------------
        # Remove our artificially added PHP
        # ----------------------------------------------------------------------
        $str = preg_replace( "#\<code\>.+?//tempstart\<br />\</span\>#is" , "<code>\n" , $str );
        $str = preg_replace( "#\<code\>.+?//tempstart\<br />#is" , "<code>\n" , $str);
        $str = preg_replace( "#//tempend.+#is" , "</span>\n</code>" , $str);
        # ----------------------------------------------------------------------
        # Replace our markers back to PHP tags.
        # ----------------------------------------------------------------------
        $str = str_replace( array( 'phptagopen', 'phptagclose', 'backslashtmp' ) , array( '&lt;?php' , '?&gt;' , '\\' ) , $str );
        $line = explode( "<br />" , rtrim( ltrim( $str , '<code>' ) , '</code>' ) );
        $result = '<div class="code"><ol>';
        foreach( $line as $key => $val ) $result .=  '<li>' . $val . '</li>';

        $result .=  '</ol></div>';
        $result = str_replace( "\n" , "" , $result );

        if ( $show !== false )
        {
            echo $result;
        }
        else
        {
            return $result;
        }
    }

    /**
    @brief      输出安全的html
    @param      String      $text
    @param      String      $tags 不允许的html标签。
    **/
    public function h( $text , $tags = null )
    {
        $text = trim( $text );
        # ----------------------------------------------------------------------
        # 完全过滤注释
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<!--?.*-->/' , '' , $text );
        # ----------------------------------------------------------------------
        # 完全过滤动态代码
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<\?|\?'.'>/' , '' , $text );
        # ----------------------------------------------------------------------
        # 完全过滤js
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<script?.*\/script>/' , '' , $text );

        $text = str_replace( '[' , '&#091;' , $text );
        $text = str_replace( ']' , '&#093;' , $text );
        $text = str_replace( '|' , '&#124;' , $text );
        # ----------------------------------------------------------------------
        # 过滤换行符
        # ----------------------------------------------------------------------
        $text = preg_replace( '/\r?\n/' , '' , $text );
        # ----------------------------------------------------------------------
        # br
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<br(\s\/)?'.'>/i' , '[br]' , $text );
        $text = preg_replace( '/(\[br\]\s*){10,}/i' , '[br]' , $text );
        # ----------------------------------------------------------------------
        # 过滤危险的属性，如：过滤on事件lang js
        # ----------------------------------------------------------------------
        while( preg_match( '/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i' , $text , $mat ) )
        $text = str_replace( $mat[0] , $mat[1] , $text );

        while( preg_match( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i' , $text , $mat ) )
        $text = str_replace( $mat[0] , $mat[1] . $mat[3] , $text );

        if ( empty( $tags ) ) $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        # ----------------------------------------------------------------------
        # 允许的HTML标签
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<(' . $tags . ')( [^><\[\]]*)>/i' , '[\1\2]' , $text );
        # ----------------------------------------------------------------------
        # 过滤多余html
        # ----------------------------------------------------------------------
        $text = preg_replace( '/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i' , '' , $text );
        # ----------------------------------------------------------------------
        # 过滤合法的html标签
        # ----------------------------------------------------------------------
        while( preg_match( '/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i' , $text , $mat ) ) $text = str_replace( $mat[0] , str_replace( '>' , ']' , str_replace( '<' , '[' , $mat[0] ) ) , $text );
        # ----------------------------------------------------------------------
        # 转换引号
        # ----------------------------------------------------------------------
        while( preg_match( '/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i' , $text , $mat ) ) $text = str_replace( $mat[0] , $mat[1] . '|' . $mat[3] . '|' . $mat[4] , $text );
        # ----------------------------------------------------------------------
        # 过滤错误的单个引号
        # ----------------------------------------------------------------------
        while( preg_match( '/\[[^\[\]]*(\"|\')[^\[\]]*\]/i' , $text , $mat ) ) $text = str_replace( $mat[0] , str_replace( $mat[1] , '' , $mat[0] ) , $text );
        # ----------------------------------------------------------------------
        # 转换其它所有不合法的 < >
        # ----------------------------------------------------------------------
        $text = str_replace( '<' , '&lt;' , $text );
        $text = str_replace( '>' , '&gt;' , $text );
        $text = str_replace( '"' , '&quot;' , $text );
        # ----------------------------------------------------------------------
        # 反转换
        # ----------------------------------------------------------------------
        $text = str_replace( '[' , '<' , $text );
        $text = str_replace( ']' , '>' , $text );
        $text = str_replace( '|' , '"' , $text );
        # ----------------------------------------------------------------------
        # 过滤多余空格
        # ----------------------------------------------------------------------
        $text = str_replace( '  ' , ' ' , $text );
        return $text;
    }

    /**
    @brief      ubb转换
    @param      String      $Text 输入类似论坛BBCode
    @return     Mix
    **/
    public function ubb( $Text )
    {
        $Text = trim( $Text );
        #$Text = htmlspecialchars( $Text );
        $Text = preg_replace( '/\\t/is' , '  ' , $Text );
        $Text = preg_replace( '/\[h1\](.+?)\[\/h1\]/is' , "<h1>\\1</h1>" , $Text );
        $Text = preg_replace( '/\[h2\](.+?)\[\/h2\]/is' , "<h2>\\1</h2>" , $Text );
        $Text = preg_replace( '/\[h3\](.+?)\[\/h3\]/is' , "<h3>\\1</h3>" , $Text );
        $Text = preg_replace( '/\[h4\](.+?)\[\/h4\]/is' , "<h4>\\1</h4>" , $Text );
        $Text = preg_replace( '/\[h5\](.+?)\[\/h5\]/is' , "<h5>\\1</h5>" , $Text );
        $Text = preg_replace( '/\[h6\](.+?)\[\/h6\]/is' , "<h6>\\1</h6>" , $Text );
        $Text = preg_replace( '/\[separator\]/is' , "" , $Text );
        $Text = preg_replace( '/\[center\](.+?)\[\/center\]/is' , "<center>\\1</center>" , $Text );
        $Text = preg_replace( '/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is' , "<a href=\"http://\\1\" target=_blank>\\2</a>" , $Text );
        $Text = preg_replace( '/\[url=([^\[]*)\](.+?)\[\/url\]/is' , "<a href=\"http://\\1\" target=_blank>\\2</a>" , $Text );
        $Text = preg_replace( '/\[url\]http:\/\/([^\[]*)\[\/url\]/is' , "<a href=\"http://\\1\" target=_blank>\\1</a>" , $Text );
        $Text = preg_replace( '/\[url\]([^\[]*)\[\/url\]/is' , "<a href=\"\\1\" target=_blank>\\1</a>" , $Text );
        $Text = preg_replace( '/\[img\](.+?)\[\/img\]/is' , "<img src=\\1>" , $Text );
        $Text = preg_replace( '/\[color=(.+?)\](.+?)\[\/color\]/is' , "<font color=\\1>\\2</font>" , $Text );
        $Text = preg_replace( '/\[size=(.+?)\](.+?)\[\/size\]/is' , "<font size=\\1>\\2</font>" , $Text );
        $Text = preg_replace( '/\[sup\](.+?)\[\/sup\]/is' , "<sup>\\1</sup>" , $Text );
        $Text = preg_replace( '/\[sub\](.+?)\[\/sub\]/is' , "<sub>\\1</sub>" , $Text );
        $Text = preg_replace( '/\[pre\](.+?)\[\/pre\]/is' , "<pre>\\1</pre>" , $Text );
        $Text = preg_replace( '/\[email\](.+?)\[\/email\]/is' , "<a href='mailto:\\1'>\\1</a>" , $Text );
        $Text = preg_replace( '/\[colorTxt\](.+?)\[\/colorTxt\]/eis' , "color_txt('\\1')" , $Text );
        $Text = preg_replace( '/\[emot\](.+?)\[\/emot\]/eis' , "emot('\\1')" , $Text );
        $Text = preg_replace( '/\[i\](.+?)\[\/i\]/is' , "<i>\\1</i>" , $Text );
        $Text = preg_replace( '/\[u\](.+?)\[\/u\]/is' , "<u>\\1</u>" , $Text );
        $Text = preg_replace( '/\[b\](.+?)\[\/b\]/is' , "<b>\\1</b>" , $Text );
        $Text = preg_replace( '/\[quote\](.+?)\[\/quote\]/is' , " <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>" , $Text );
        $Text = preg_replace( '/\[code\](.+?)\[\/code\]/eis' , "highlight_code('\\1')" , $Text );
        $Text = preg_replace( '/\[php\](.+?)\[\/php\]/eis' , "highlight_code('\\1')" , $Text);
        $Text = preg_replace( '/\[sig\](.+?)\[\/sig\]/is' , "<div class='sign'>\\1</div>" , $Text );
        $Text = preg_replace( '/\\n/is' , '<br/>' , $Text );
        return $Text;
    }

    /**
    @brief      过滤xss数据
    @param      String      $val 要过滤的字符串
    @return     String
    **/
    public function remove_xss( $val )
    {
        $val = preg_replace( '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/' , '' , $val );
        # ----------------------------------------------------------------------
        # a-z小写
        # ----------------------------------------------------------------------
        $search = 'abcdefghijklmnopqrstuvwxyz';
        # ----------------------------------------------------------------------
        # A-Z大写
        # ----------------------------------------------------------------------
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        # ----------------------------------------------------------------------
        # 数字、特殊符号
        # ----------------------------------------------------------------------
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ( $i = 0; $i < strlen( $search ); $i++ )
        {
            $val = preg_replace( '/(&#[xX]0{0,8}' . dechex( ord( $search[$i] ) ) . ';?)/i', $search[$i], $val );
            $val = preg_replace( '/(&#0{0,8}' . ord( $search[$i] ) . ';?)/' , $search[$i] , $val );
        }
        # ----------------------------------------------------------------------
        # now the only remaining whitespace attacks are \t, \n, and \r
        # ----------------------------------------------------------------------
        $ra1 = array
        (
            'javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml',
            'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe',
            'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'
        );
        $ra2 = array
        (
            'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate',
            'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste',
            'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange',
            'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable',
            'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend',
            'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate',
            'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown',
            'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
            'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup',
            'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange',
            'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter',
            'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange',
            'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'
        );
        $ra = array_merge( $ra1 , $ra2 );
        # ----------------------------------------------------------------------
        # keep replacing as long as the previous round replaced something
        # ----------------------------------------------------------------------
        $found = true;
        while ( $found == true )
        {
            $val_before = $val;
            for ( $i = 0; $i < sizeof( $ra ); $i++ )
            {
                # --------------------------------------------------------------
                # 组合正规化语法
                # --------------------------------------------------------------
                $pattern = '/';
                # --------------------------------------------------------------
                # 迴圈处理组合
                # --------------------------------------------------------------
                for ( $j = 0; $j < strlen( $ra[$i] ); $j++ )
                {
                    if ( $j > 0 )
                    {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                # --------------------------------------------------------------
                # add in <> to nerf the tag
                # --------------------------------------------------------------
                $replacement = substr( $ra[$i] , 0 , 2 ) . '<x>' . substr( $ra[$i] , 2 );
                # --------------------------------------------------------------
                # filter out the hex tags
                # --------------------------------------------------------------
                $val = preg_replace( $pattern , $replacement , $val );
                if ( $val_before == $val )
                {
                    # ----------------------------------------------------------
                    # no replacements were made, so exit the loop
                    # ----------------------------------------------------------
                    $found = false;
                }
            }
        }
        return $val;
    }

    /**
    @brief      转换 HTML 特殊字符以及空格和换行符
    @remarks    一般将 &lt;textarea&gt; 标记中输入的内容从数据库中读出来后在网页中显示
    @param      String      $text
    **/
    public function toHtml( $text )
    {
        $text =  htmlspecialchars( $text );
        $text =  nl2br( str_replace( ' ', '&nbsp;' , $text ) );
        return $text;
    }

    /**
    @brief      將指定檔案讀出，並轉換成 Data Uri 的方式
    @param      String      $filename 檔案名稱
    @retval     String
    @remarks    使用方式
    @code{.unparsed}
    $this->Core_Widget->get_DataUri( 'all.css' );
    @endcode
    @code
    data:text/css;base64,77u/aW1nICANCnsN
    @endcode
    **/
    public function get_DataUri( $filename )
    {
        if ( function_exists( 'mime_content_type' ) )
        {
            $mime = mime_content_type($filename);
        }
        else
        {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $mime  = finfo_file( $finfo , $filename );
        }

        return 'data:' . $mime . ';base64,' . base64_encode( file_get_contents( $filename ) );
    }
}