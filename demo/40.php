<?php
ini_set("memory_limit", "1024M");
require '../include/init.php';
use Maple\PhpSpider\PhpSpider;
use Maple\Utils\Log;
$configs = [
    'name' => 'nanren40',
    'domains' => [
        'www.nanren40.com'
    ],
//    'interval'=>1000,
    'collect_fails' => 2,
    'tasknum' => 4,
    'save_running_state' => true,
    'scan_urls' => [
        "http://www.nanren40.com/zhengmei/",
        "http://www.nanren40.com/zhaizhai/"
    ],
    'list_url_regex' => [
        "http://www.nanren40.com/zhengmei/list_2_\d+.html",
        "http://www.nanren40.com/zhaizhai/list_1_\d+.html"
    ],
    'content_url_regex' => [
        "http://www.nanren40.com/zhaizhai/\d{4}.html",
        "http://www.nanren40.com/zhengmei/\d{4}.html"
    ],
    'export' => [
        'type' => 'db',
        'table' => 'nanren40',
    ],
    'fields' => [
        // 标题
        [
            'name' => "name",
            'selector' => "//header/h1",
            'required' => true
        ],
        // 发布时间
        [
            'name' => "time",
            'selector' => "//span[contains(@class,'publisherDate')]/time",
            'required' => true
        ],
        // 内容
        [
            'name' => "content",
            'selector' => "//div[contains(@class, 'dede_pages')]//a[not(@href='#')]//@href", //分页
            'repeated' => true,
            'required' => true,
            'children' => [
                [
                    // 抽取出其他分页的url待用
                    'name' => 'content_page_url',
                    'selector' => "//text()"
                ],
                [
                    // 抽取其他分页的内容
                    'name' => 'page_content',
                    // 发送 attached_url 请求获取其他的分页数据
                    // attached_url 使用了上面抓取的 content_page_url
                    'source_type' => 'attached_url',
                    'attached_url' => 'content_page_url',
                    'selector' => "//div[@id='article_content']/img"
                ]
            ]
        ]
    ]
];

$spider = new PhpSpider($configs);

$spider->onExtractField = function ($fieldname, $data) {
    if ($fieldname == 'name') {
//        $data = trim(preg_replace("#\(.*?\)#", "", $data));
    }
    if ($fieldname == 'time') {
//        $data = mb_substr($data, 5, 15);
    } elseif ($fieldname == 'content') {
        var_dump($data);
        $contents = $data;
        $array = [];
        foreach ($contents as $content) {
            $url = $content['page_content'];
            // 以纳秒为单位生成随机数
            $filename = uniqid() . ".jpg";
            // 在data目录下生成图片
            $filepath = PATH_DATA . "/images/man40/{$filename}";
            // 用系统自带的下载器wget下载
            exec("wget {$url} -O {$filepath}");
            $array[] = $filename;
        }
        $data = implode(",", $array);
    }
    return $data;
};
//$res = $spider->request('http://www.nanren40.com/zhengmei');
//var_dump($res);
$spider->start();
