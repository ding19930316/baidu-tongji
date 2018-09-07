<h1 align="center"> baidu-tongji </h1>

<p align="center"> 百度统计数据导出服务 Tongji API </p>


## 安装

```shell
$ composer require qqjt/baidu-tongji
```

## 使用

```php
use Qqjt\BaiduTongji\Auth;
use Qqjt\BaiduTongji\Report;

$accountType = 1; //ZhanZhang:1,FengChao:2,Union:3,Columbus:4
$username = 'xxxx';
$password = 'yyyy';
$token = 'zzzz';
$uuid = 'abcd1234';

$auth = new Auth($accountType, $username, $password, $token, $uuid);

$res = $auth->login();

$ucid = $res['ucid'];
$st = $res['st'];

$report = new Report($accountType, $username, $token, $uuid, $ucid, $st);

$siteRes = $report->getSiteList();

var_dump($siteRes);
$siteList = $siteRes['body']['data'][0]['list'];
$siteId = $siteList[0]['site_id'];

$parameters = ['site_id' => $siteId,        //站点ID
    'method' => 'trend/time/a',             //趋势分析报告
    'start_date' => '20160501',             //所查询数据的起始日期
    'end_date' => '20160531',               //所查询数据的结束日期
    'metrics' => 'pv_count,visitor_count',  //所查询指标为PV和UV
    'max_results' => 0,                     //返回所有条数
    'gran' => 'day',                        //按天粒度
];

$dataRes = $report->getData($parameters);
var_dump($dataRes);
```

## License

MIT