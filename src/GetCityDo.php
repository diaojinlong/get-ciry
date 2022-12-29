<?php

namespace Diaojinlong\GetCity;

use GuzzleHttp\Client;
use voku\helper\HtmlDomParser;

class GetCityDo
{
    //域名
    private $baseUri = 'http://www.stats.gov.cn';

    //首页路径
    private $homePath = '/tjsj/tjbz/tjyqhdmhcxhfdm/2021/index.html';

    //其他页面路径
    private $basePath = '/tjsj/tjbz/tjyqhdmhcxhfdm/2021/';

    //请求每个页面休息秒数
    private $sleepSecond = 0;

    //获取省市区Json数据
    public function getJsonData($sleepSecond = 0)
    {
        $this->sleepSecond = $sleepSecond;
        //获取首页省份信息
        $html = $this->getHtml($this->homePath);
        //解析省份信息
        $provinceDom = HtmlDomParser::str_get_html($html);
        //获取省份信息
        $provinces = $provinceDom->find('.provincetr a');
        $data = [];
        foreach ($provinces as $province) {
            //获取省份名称
            $provinceName = strip_tags($province->innertext());
            //获取城市页面路径
            $cityPath = $province->getAttribute('href');
            //获取省份编号
            $provinceId = str_pad(str_replace('.html', '', $cityPath), 6, '0', STR_PAD_RIGHT);
            //省份信息
            $provinceData = [
                'id' => $provinceId,
                'name' => $provinceName,
                'pid' => 0,
                'next' => []
            ];
            $cityPath != '' && $this->getCity($provinceData, $cityPath);
            $data[] = $provinceData;
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取城市
     * @param $provinceData
     * @param $cityPath
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getCity(&$provinceData, $cityPath)
    {
        //获取省份下的城市列表
        $cityDom = HtmlDomParser::str_get_html($this->getHtml($this->basePath . $cityPath));
        //获取所有城市
        $cityTrList = $cityDom->find('.citytr');
        //循环城市
        foreach ($cityTrList as $cityTr) {
            //城市页面所有的a标签
            $cityTrTags = $cityTr->find('a');
            $cityData = [
                'id' => '',
                'name' => '',
                'pid' => $provinceData['id'],
                'next' => []
            ];
            $areaPath = '';
            //循环获取当前城市信息
            foreach ($cityTrTags as $Key => $cityTrTag) {
                $text = $cityTrTag->innertext();
                if ($Key == 0) {
                    $cityData['id'] = substr($text, 0, 6);
                    $areaPath = $cityTrTag->getAttribute('href');
                } else {
                    $cityData['name'] = $text;
                }
            }
            $areaPath != '' && $this->getArea($cityData, $areaPath);
            $provinceData['next'][] = $cityData;
        }
    }

    /**
     * 获取地区
     * @param $cityData
     * @param $areaPath
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getArea(&$cityData, $areaPath)
    {
        //获取地区页面Dom
        $areaDom = HtmlDomParser::str_get_html($this->getHtml($this->basePath . $areaPath));
        $areaTrList = $areaDom->find('.countytr');
        foreach ($areaTrList as $areaTr) {
            $areaTdTags = $areaTr->find('td');
            $areaData = [
                'id' => '',
                'name' => '',
                'pid' => $cityData['id']
            ];
            foreach ($areaTdTags as $key => $areaTd) {
                $text = $areaTd->text();
                if ($key == 0) {
                    $areaData['id'] = substr($text, 0, 6);
                } else {
                    $areaData['name'] = $text;
                }
            }
            $cityData['next'][] = $areaData;
        }
    }


    /**
     * 获取页面内容
     * @param $path
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getHtml($path)
    {
        $this->sleepSecond > 0 && sleep($this->sleepSecond);
        $client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 10
        ]);
        $response = $client->get($path, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1'
            ]
        ]);
        return (string)$response->getBody();
    }
}