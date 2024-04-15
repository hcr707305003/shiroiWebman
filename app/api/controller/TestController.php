<?php

namespace app\api\controller;

use app\common\model\LaravelModel;
use app\common\model\UserNotification;
use app\common\plugin\FormDesign;
use app\common\plugin\TableHandle;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use hg\apidoc\annotation\Method;
use hg\apidoc\annotation\Param;
use hg\apidoc\annotation\Title;
use mysqli;
use support\Db;
use support\Response;
use hg\apidoc\annotation as Apidoc;
use think\db\connector\Mysql;
use Webman\RedisQueue\Client;

/**
 * @Title("测试")
 */
class TestController extends ApiBaseController
{
    protected array $loginExcept = [
        'api/test/test',
        'api/test/test_form_design',
        'api/test/test_queue',
        'api/test/test_encode',
        'api/test/test_qrcode',
        'api/test/test_think_orm',
        'api/test/test_laravel_orm',
    ];


    /**
     * https://docs.apidoc.icu/guide/update5.html#_2%E3%80%81%E8%AF%B7%E6%B1%82%E4%BA%8B%E4%BB%B6%E6%B3%A8%E8%A7%A3%E5%8F%98%E5%8C%96
     * https://docs.apidoc.icu/use/function/parsesAnnotation.html
     * @Apidoc\Title ("V5版本的子参数嵌套注解写法")
     * @Apidoc\Before(event="ajax",value="body.",url="/demo/test/getFormToken",method="POST",before={
     *    @Apidoc\Before(event="setBody",key="formToken",value="123")
     * },after={
     *    @Apidoc\After(event="setHeader",key="X-CSRF-TOKEN",value="res.data.data")
     * })
     * @Apidoc\Method("put")
     * @Apidoc\Param("list",type="array", desc="对象数组",dict="testType",childrenType="object",children={
     *     @Apidoc\Param("name",type="string",dict="testType",desc="名称"),
     *     @Apidoc\Param("code",type="int",desc="编号"),
     * })
     */
    public function test()
    {

    }

    /**
     * 测试rpc远程调用
     * @example 请求地址: http://127.0.0.1:1111/api/test/test_rpc
     * @return void
     */
    public function test_rpc()
    {
        $client = stream_socket_client('tcp://127.0.0.1:3344');
        $request = [
            'class'   => 'app\\common\\model\\User',
            'method'  => 'findOrEmpty',
            'args'    => [1], // 100 是 $uid
        ];
        fwrite($client, json_encode($request)."\n"); // text协议末尾有个换行符"\n"
        $result = fgets($client, 10240000);
        $result = json_decode($result, true);
        dump($result);
    }

    /**
     * 测试表单生成
     * @return void
     */
    public function test_form_design()
    {
        //form表单生成
        $form = FormDesign::getInstance()->generate([
            [
                'type' => 'text',
                'max_limit' => 200,
                'field' => 'company'
            ]
        ]);
        //实例化
        $table = new TableHandle('test');
        //数据表格生成
        $table->generate($form);
        $table->save($form);

        dump($form);
    }

    /**
     * 测试redis异步推送
     * @return void
     */
    public function test_queue()
    {
        Client::send('work', ['aa' => 11, 'bb' => 222]);
    }

    /**
     * 测试加密解密
     * @return void
     */
    public function test_encode()
    {
        //加密的字符串为:测试 加密类型:ENCODE 密钥为项目名:oa 失效期为:30秒
        $encode_str = auth_code('user_id=1&company_id=1', 'ENCODE', 'oa', 30);
        dump($encode_str);
        //解密字符串
        $decode_str = auth_code($encode_str, 'DECODE', 'oa');
        dump($decode_str);
    }

    /**
     * 测试生成二维码
     * @return Response
     */
    public function test_qrcode(): Response
    {
        $result = Builder::create()
            ->data('https://www.baidu.com')
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->logoPath(public_path('static/admin/images/attachments.png'))
            ->logoResizeToWidth(30)
            ->build();
        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
        ]);
    }

    /**
     * 测试think orm读写连贯操作
     * @return void
     */
    public function test_think_orm()
    {
        $table = \think\facade\Db::table('test');
        //插入数据
        $table->insert([
            'username' => $username = uniqid()
        ]);
        d($table->where('username', $username)->findOrEmpty());
    }

    /**
     * 测试laravel orm读写连贯操作
     * @return void
     */
    public function test_laravel_orm()
    {
        $table = \support\Db::table('test');
        //插入数据
        $table->insert([
            'username' => $username = uniqid()
        ]);
        d($table->where('username', $username)->first());
    }
}