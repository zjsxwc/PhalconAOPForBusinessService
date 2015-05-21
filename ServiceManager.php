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
    private static $pointCutsHash = [];

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

    protected function getPointCuts($m)
    {
        if (isset(self::$pointCutsHash[$this->protoName . '|' . $m])) {
            return self::$pointCutsHash[$this->protoName . '|' . $m];
        }

        self::$pointCutsHash[$this->protoName . '|' . $m] = [];

        foreach (self::$pointCuts as $pointCut) {
            if (preg_match($pointCut[0], $this->protoName) and preg_match($pointCut[1], $m)) {
                self::$pointCutsHash[$this->protoName . '|' . $m][$pointCut[2]][] = $pointCut;
            }
        }

        return self::$pointCutsHash[$this->protoName . '|' . $m];
    }

    public function __call($m, $a)
    {
        $pc = $this->getPointCuts($m);

        if ($pc) {
            //round point
            if (isset($pc['round'])) {
                foreach ($pc['round'] as $pc) {
                    $pc[3]($m, $a);
                }
            }

            //before point
            if (isset($pc['before'])) {
                foreach ($pc['before'] as $pc) {
                    $pc[3]($m, $a);
                }
            }

            $result = call_user_func_array([$this->proto, $m], $a);

            //after point
            if (isset($pc['after'])) {
                foreach ($pc['after'] as $pc) {
                    $pc[3]($m, $a, $result);
                }
            }

            //round point
            if (isset($pc['round'])) {
                foreach ($pc['round'] as $pc) {
                    $pc[3]($m, $a, $result);
                }
            }

            return $result;
        } else {
            return call_user_func_array([$this->proto, $m], $a);
        }
    }
}
