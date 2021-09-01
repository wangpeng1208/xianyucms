<?php
namespace app\push\controller;
use think\worker\Server;
use Workerman\Lib\Timer;
class Task extends Server {
    protected $socket = 'websocket://0.0.0.0:2369';
    protected $processes = 8;//进程数

    public $connectionArr = array();

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        //{"event": "sendData",  "data": "[1,2,,3,4,5]"}
        $wsData = json_decode($data,true);
        if(!isset($wsData['option'])){
            $connection->send('接收数据:'.$data);
            return;
        }
        switch($wsData['option']){
            case "setName"://如果是接收数据
                $this->setName($connection,$wsData['name']);
                break;
            case "getOnline"://如果是接收数据
                $this->getOnline($connection);
                break;
            case "sendMsg"://如果是接收数据
                $this->sendMsg($connection,$wsData);
                break;
            default:
                $connection->send('其他数据');
                break;
        }
    }

    //如果是发送消息
    public function sendMsg($connection,$data){
        $msgStr = json_encode(array("option"=>"getMsg","msg"=>"来自".$connection->nickname."的消息:".$data['msgContent']));
        $this->connectionArr[$data['toUser']]->send($msgStr);
    }

    //获取在线用户
    public function getOnline($connection){
        $users = array();
        foreach($this->connectionArr as $keyId=>$conn){
            $users[] = array("keyId"=>$keyId,"nickname"=>$conn->nickname);
        }
        $msgStr = json_encode(array("option"=>"getOnline","users"=>$users));
        $connection->send($msgStr);
    }

    //设置姓名
    public function setName($connection,$uName){
        $connection->nickname = $uName;

        //通知其他用户有用户上线了..
        foreach($this->connectionArr as $keyId=>$conn){
            $msgStr = json_encode(array("option"=>"onLine","msg"=>$conn->nickname." 进入了频道..."));
            $conn->send($msgStr);
        }
        $this->connectionArr[$connection->keyId] = $connection;
        $msgStr = json_encode(array("option"=>"setName","msg"=>"当前用户名称设置为:".$uName));
        $connection->send($msgStr);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        //给新连接进来的socket一个唯一的ID
        $keyId = uniqid().mt_rand(10000,99999);
        $connection->keyId = $keyId;
        $connection->nickname = "游客";
        $this->connectionArr[$keyId] = $connection;
    }

    /**
     * 当连接断开销毁连接对象节约内存空间
     * @param $connection
     */
    public function onClose($connection)
    {
        unset($this->connectionArr[$connection->keyId]);

        //通知其他人用户离线了
        foreach($this->connectionArr as $keyId=>$conn){
            $msgStr = json_encode(array("option"=>"outLine","msg"=>$conn->nickname." 离开了频道..."));
            $conn->send($msgStr);
        }
        unset($connection);
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onerror($connection, $code, $msg)
    {
        echo "\r\n error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        echo "\r\n process start";
    }

//     当连接断开时触发的回调函数 终止timer事件
//    public function onClose($connection)
//    {
//        Timer::del($connection->timer_id);
//        echo "\r\n process close $connection->timer_id";
//    }
//    Timer事件
//    public function eventGetData($connection){
//        $time_interval = 1;
//        $connection->timer_id = Timer::add($time_interval,function()use($connection)
//        {
//            $id = $connection->lastId;
//            $sql = "SELECT * FROM ccgisdata WHERE id > {$id} ORDER BY id";
//            $dataStr = pdo_fetch($sql);
//            $connection->lastId = $dataStr['id'];
//            $connection->send($dataStr['data']);
//        });
//    }
}