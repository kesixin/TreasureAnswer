<?php
namespace Common\Lib;
/**
 * 并发处理类
 * memcache
 * 队列处理
 * author universe.h 2017.11.10
 */
class Concurrent {
    protected $conf;
    protected $data;

    /**
     * 初始化配置
     * Ffmpeg constructor.
     */
    public function __construct($data)
    {
        $this->conf = [
            'prefix' => 'user',
            'expro' => 86400*30,
        ];
        $this->data = $data;
    }


    public function setCache(){
        S( $this->data['pid'],$this->data['info'], ['expire'=>$this->conf['expro']]);


        //更新随机红包个数
        S('moneyList'.$pid,$moneyList,['expire'=>$expire]);

        S( $this->data['pid']);

    }
}