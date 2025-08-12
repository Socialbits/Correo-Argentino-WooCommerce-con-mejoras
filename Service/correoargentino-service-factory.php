<?php

class CorreoArgentinoServiceFactory
{
    private $serviceType;

    private $settings;
    public function __construct()
    {
        $this->serviceType = Utils::getCurrentServiceType();
        if (!isset($this->serviceType)) {
            throw new Exception("Tipo de servicio indefinido", 1);
        }
    }
    public function get()
    {
        if ($this->serviceType == MI_CORREO) {
            return new CorreoArgentinoMiCorreoService();
        }
        if ($this->serviceType == PAQ_AR) {
            return new CorreoArgentinoPaqArService();
        }
    }
}