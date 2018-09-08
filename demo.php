<?php
require __DIR__ . '/vendor/autoload.php';

use Qqjt\BaiduTongji\Report;

$config = [
    /**
     * 1：站长账号
     * 2：凤巢账号
     * 3：联盟账号
     * 4：哥伦布账号
     */
    'account_type' => 1,
    'username' => 'aaa',
    'password' => 'bbb',
    'token' => 'ccc',
    'uuid' => 'ddd',
];

$report = new Report($config);

$siteRes = $report->getSiteList();
var_dump($siteRes);

$siteList = $siteRes['body']['data'][0]['list'];
$siteId = $siteList[0]['site_id'];
$parameters = [
    'site_id' => $siteId,                   //站点ID
    'method' => 'trend/time/a',             //趋势分析报告
    'start_date' => '20160501',             //所查询数据的起始日期
    'end_date' => '20160531',               //所查询数据的结束日期
    'metrics' => 'pv_count,visitor_count',  //所查询指标为PV和UV
    'max_results' => 0,                     //返回所有条数
    'gran' => 'day',                        //按天粒度
];
$dataRes = $report->getData($parameters);
var_dump($dataRes);