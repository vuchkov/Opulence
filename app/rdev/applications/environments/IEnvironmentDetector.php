<?php
/**
 * Copyright (C) 2015 David Young
 * 
 * Defines the interface for environment detectors to implement
 */
namespace RDev\Applications\Environments;

interface IEnvironmentDetector
{
    /**
     * Gets the environment the server belongs to, eg "production"
     *
     * @return string The environment the server belongs to
     */
    public function detect();

    /**
     * Registers a host for a particular environment name
     *
     * @param string $environmentName The name of the environment this host belongs to
     * @param HostName|HostName[] $hosts The host or hosts to add
     */
    public function registerHost($environmentName, $hosts);
}