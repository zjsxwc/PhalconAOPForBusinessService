<?php

namespace xx\yy;

use Phalcon\Mvc\User\Component;

class ServiceManager extends Component
{
    protected static $services = [];

    public function getService($serviceName, $nameSpace = 'xx\yy\Service')
    {
        $fullServiceName = $nameSpace . '\\' . $serviceName;
        if (isset(self::$services[$fullServiceName])) {
            return $services[$fullServiceName];
        } else {
            $services[$fullServiceName] = new Decorator(new $fullServiceName);
            return $services[$fullServiceName];
        }
    }
}

//一个简单的AOP实现

class Decorator
{
    private $proto;
    public $protoName;

    private static $pointCuts = [];
    private static $pointCutHash = [];

    protected function genPointCuts()
    {
        if (count(self::$pointCuts) > 0) {
            return;
        }
        self::$pointCuts = include __DIR__ . "/PointCuts.php";
    }

    public function __construct($proto)
    {
        $this->proto = $proto;
        $this->protoName = get_class($proto);
        $this->genPointCuts();
    }

    protected function getPointCut($m)
    {
        if (isset(self::$pointCutHash[$this->protoName . '|' . $m])) {
            return self::$pointCutHash[$this->protoName . '|' . $m];
        }

        foreach (self::$pointCuts as $pointCut) {
            if (preg_match($pointCut[0], $this->protoName) and preg_match($pointCut[1], $m)) {
                self::$pointCutHash[$this->protoName . '|' . $m] = $pointCut;
                return $pointCut;
            }
        }
        self::$pointCutHash[$this->protoName . '|' . $m] = false;
        return false;
    }

    public function __call($m, $a)
    {
        $pc = $this->getPointCut($m);

        if ($pc) {
            //round point
            if ($pc[2] == 'round') {
                $pc[3]($m);
            }
            //before point
            if ($pc[2] == 'before') {
                $pc[3]($m);
            }
            $result = call_user_func_array([$this->proto, $m], $a);
            //after point

            if ($pc[2] == 'after') {
                $pc[3]($m);
            }
            //round point
            if ($pc[2] == 'round') {
                $pc[3]($m);
            }
            return $result;
        } else {
            return call_user_func_array([$this->proto, $m], $a);
        }
    }
}
