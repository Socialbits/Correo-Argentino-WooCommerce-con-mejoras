<?php

interface CorreoArgentinoService
{
    public function login();
    public function setHeaders($headers);
    public function registerOrder($orderId);
}

interface CorreoArgentinoServiceMiCorreoInterface extends CorreoArgentinoService
{
    public function getRates($postalCode, $deliveryType, $dimensions);
}
interface CorreoArgentinoServicePaqArInterface extends CorreoArgentinoService
{
    public function cancel($tracking);
    public function label($tracking);
    public function getRates($postalCode, $deliveryType, $dimensions);
}