<?php
return [
    'enable'  => true,
    'apidoc' => [
        //自定义处理注解
        'parsesAnnotation' => function($data){
            // 这里面按自己的业务需求写逻辑
            if (!empty($data['dict'])){
                $data['md'] =  view($data['dict'], $data)->rawBody();
            }
            return $data;
        },
        // （选配）文档标题，显示在左上角与首页
        'title'              => '接口文档',
        // （选配）文档描述，显示在首页
        'desc'               => '',
        // （必须）设置文档的应用/版本
        'apps'           => [
            [
                // （必须）标题
                'title'=>'Admin接口',
                // （必须）控制器目录地址
                'path'=>'app\admin\controller',
                // （必须）唯一的key
                'key'=>'admin',
            ],
            [
                // （必须）标题
                'title'=>'Api接口',
                // （必须）控制器目录地址
                'path'=>'app\api\controller',
                // （必须）唯一的key
                'key'=>'api',
            ]
        ],
        // （必须）指定通用注释定义的文件地址
        'definitions'        => "app\common\controller\Definitions",
        // （必须）自动生成url规则，当接口不添加@Apidoc\Url ("xxx")注解时，使用以下规则自动生成
        'auto_url' => [
            // 字母规则，lcfirst=首字母小写；ucfirst=首字母大写；
            'letter_rule' => "lcfirst",
            // url前缀
            'prefix'=>"",
            'custom' =>function($class,$method){
                // 使用反射获取类的属性值
                $reflectionClass = new ReflectionClass($class);
                $instance = $reflectionClass->newInstanceWithoutConstructor();
                //获取是否显示文档
                $show_doc_property = $reflectionClass->getProperty('show_doc');
                $show_doc_property->setAccessible(true);
                /** @var array $show_doc 显示文档 */
                $show_doc = $show_doc_property->getValue($instance) ?: [];
                if(empty($show_doc) || !in_array($method,$show_doc)) {

                }
//                dump($show_doc); //获取需要显示的文档
                //处理指定方法的操作
                $methods = ['read', 'show', 'update', 'delete', 'destroy'];

                $classPath = explode('\\', $class);
                $shortClass = small_mount_to_underline(remove_both_str(end($classPath), 'Controller', 2));
                if(in_array($method, $methods)) {
                    $shortMethod = '{id}';
                } else {
                    $shortMethod = small_mount_to_underline($method);
                }
                return "/{$classPath[1]}/{$shortClass}" . ($shortMethod == 'edit' ? '': "/{$shortMethod}");
            },
        ],
        // （选配）是否自动注册路由
        'auto_register_routes'=>false,
        // （必须）缓存配置
        'cache'              => [
            // 是否开启缓存
            'enable' => false,
        ],
        // （必须）权限认证配置
        'auth'               => [
            // 是否启用密码验证
            'enable'     => false,
            // 全局访问密码
            'password'   => "123456",
            // 密码加密盐
            'secret_key' => "apidoc#hg_code",
            // 授权访问后的有效期
            'expire' => 24*60*60
        ],
        // 全局参数
        'params'=>[
            // （选配）全局的请求Header
            'header'=>[
                // name=字段名，type=字段类型，require=是否必须，default=默认值，desc=字段描述
                ['name'=>'token','type'=>'string','require'=>true,'desc'=>'身份令牌Token'],
            ],
            // （选配）全局的请求Query
            'query'=>[
                // 同上 header
            ],
            // （选配）全局的请求Body
            'body'=>[
                // 同上 header
            ],
        ],
        // 全局响应体
        'responses'=>[
            // 成功响应体
            'success'=>[
                ['name'=>'code','desc'=>'代码','type'=>'int','require'=>1],
                ['name'=>'msg','desc'=>'信息','type'=>'string','require'=>1],
                //参数同上 headers；main=true来指定接口Returned参数挂载节点
                ['name'=>'data','desc'=>'数据','main'=>true,'type'=>'object','require'=>1],
            ],
            // 异常响应体
            'error'=>[
                ['name'=>'code','desc'=>'代码','type'=>'int','require'=>1,'md'=>'/docs/HttpError.md'],
                ['name'=>'msg','desc'=>'信息','type'=>'string','require'=>1],
                ['name'=>'data','desc'=>'数据','main'=>true,'type'=>'object','require'=>1],
            ]
        ],
        //（选配）默认作者
        'default_author'=>'shiroi',
        //（选配）默认请求类型
        'default_method'=>'GET',
        //（选配）Apidoc允许跨域访问
        'allowCrossDomain'=>true,
        /**
         * （选配）解析时忽略带@注解的关键词，当注解中存在带@字符并且非Apidoc注解，如 @key test，此时Apidoc页面报类似以下错误时:
         * [Semantical Error] The annotation "@key" in method xxx() was never imported. Did you maybe forget to add a "use" statement for this annotation?
         */
        'ignored_annitation'=>[],

        // （选配）数据库配置
        'database'=>[],
        // （选配）Markdown文档
        'docs'              => [
            [
                'title'=>'README',
                'path'=> 'README'
            ],
            [
                'title' => '版本维护',
                'children'=>[
                    ['title'=>'Product-V0.0.1版本','path'=>'docs/Product-V0.0.1'],
                ],
            ]
        ],
        // （选配）接口生成器配置 注意：是一个二维数组
        'generator' =>[]
    ]
];