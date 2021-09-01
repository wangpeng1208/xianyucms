<?php

namespace app\push\command;
use Workerman\Worker;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
class Workerman extends Command
{
    protected function configure()
    {
        $this->setName('workerman')
        ->addArgument('action', Argument::OPTIONAL, "action  start|stop|restart")
        ->addArgument('type', Argument::OPTIONAL, "d -d")
        ->setDescription('workerman chat');
    }
    protected function execute(Input $input, Output $output)
    {
        global $argv;
        $action = trim($input->getArgument('action'));
        $type   = trim($input->getArgument('type')) ? '-d' : '';
        $argv[0] = 'chat';
        $argv[1] = $action;
        $argv[2] = $type ? '-d' : '';
        $this->start();
    }
    private function start()
    {
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        Worker::runAll();
    }
    private function startBusinessWorker()
    {
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = 'YourAppBusinessWorker';
        // bussinessWorker进程数量
        $worker->count = 4;
        //设置处理业务的类,此处制定Events的命名空间
        $worker->eventHandler= \app\push\controller\Events::class;
        // 服务注册地址
        $worker->registerAddress = '127.0.0.1:1238';
    }
    private function startGateWay()
    {
        // gateway 进程，这里使用Text协议，可以用telnet测试
        $gateway = new Gateway("websocket://0.0.0.0:8282");
        // gateway名称，status方便查看
        $gateway->name = 'YourAppGateway';
        // gateway进程数
        $gateway->count = 4;
        // 本机ip，分布式部署时使用内网ip
        $gateway->lanIp = '127.0.0.1';
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = 20003;
        // 服务注册地址
        $gateway->registerAddress = '127.0.0.1:1238';
        // 心跳间隔
        $gateway->pingInterval = 6;
        $gateway->pingNotResponseLimit = 1;
        // 心跳数据
        $gateway->pingData = '';
    }

    private function startRegister()
    {
        new Register('text://0.0.0.0:1238');
    }
}