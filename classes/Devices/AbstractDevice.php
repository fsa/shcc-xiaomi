<?php

namespace FSA\XiaomiPlugin\Devices;

use FSA\XiaomiPlugin\XiaomiPacket,
    FSA\SmartHome\DeviceInterface;

abstract class AbstractDevice implements DeviceInterface
{
    protected $sid;
    protected $model;
    protected $voltage;
    protected $updated;
    protected $cmd;
    protected $events;

    public function __construct()
    {
        $this->events = [];
        $this->updated = 0;
    }

    public function init($device_id, $init_data): void
    {
        $this->sid = $device_id;
        foreach ($init_data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getInitDataList(): array
    {
        return [];
    }

    public function getInitDataValues(): array
    {
        return [];
    }

    public function update(XiaomiPacket $pkt)
    {
        $this->events = [];
        $this->sid = $pkt->getSid();
        $this->cmd = $pkt->getCmd();
        $this->model = $pkt->getModel();
        foreach ($pkt->getData() as $param => $value) {
            switch ($param) {
                case "voltage":
                    $this->setVoltage($value);
                    break;
                default:
                    $this->updateParam($param, $value);
            }
        }
        $this->updated = time();
    }

    protected function setVoltage($value)
    {
        $last = $this->voltage;
        $this->voltage = $value / 1000;
        if ($this->voltage != $last) {
            $this->events['voltage'] = $this->voltage;
        }
    }

    public function getEvents(): ?array
    {
        if (sizeof($this->events) == 0) {
            return null;
        }
        return $this->events;
    }

    public function getHwid(): string
    {
        return $this->sid;
    }

    public function getLastUpdate(): int
    {
        return $this->updated;
    }

    public function getVoltage()
    {
        return $this->voltage;
    }

    protected function showUnknownParam($param, $value)
    {
        printf('%s=>{%s=%s}', $this->getHwid(), $param, $value);
    }

    abstract protected function updateParam($param, $value);
}
