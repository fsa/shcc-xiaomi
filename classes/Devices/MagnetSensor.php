<?php

/**
 * Датчик двери/окна Xiaomi
 */

namespace FSA\XiaomiPlugin\Devices;

class MagnetSensor extends AbstractDevice
{
    private $status;
    private $open_timer;

    protected function updateParam($param, $value)
    {
        switch ($param) {
            case "status":
                $this->setStatus($value);
                break;
            case "no_close":
                $this->setNoClose($value);
                break;
            default:
                $this->showUnknownParam($param, $value);
        }
    }

    private function setStatus(string $value)
    {
        if ($value == 'close') {
            $this->open_timer = null;
        }
        $last = $this->status;
        $this->status = $value;
        if ($last != $value) {
            $this->events['status'] = $value;
            $this->events['alarm'] = $value != 'close';
        }
    }

    private function setNoClose(string $value)
    {
        $this->open_timer = $value;
        $this->events['no_close'] = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDescription(): string
    {
        return "Xiaomi Smart Door and Windows Sensor";
    }


    public function getState(): array
    {
        return [
            'status' => $this->status,
            'voltage' => $this->voltage
        ];
    }

    public function __toString(): string
    {
        $result = [];
        switch ($this->status) {
            case null:
                break;
            case "open":
                $result[] = "Открыто.";
                break;
            case "close":
                $result[] = "Закрыто.";
                break;
            default:
                $result[] = "Статус " . $this->status . '.';
        }
        if ($this->open_timer) {
            $result[] = 'Открыто более ' . $this->open_timer . ' с.';
        }
        if ($this->voltage) {
            $result[] = sprintf('Батарея CR2032: %.3f В.', $this->voltage);
        }
        return join(' ', $result);
    }

    public function getEventsList(): array
    {
        return ['alarm', 'status', 'voltage'];
    }
}
