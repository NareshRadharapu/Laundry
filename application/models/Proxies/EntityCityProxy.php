<?php

namespace Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class EntityCityProxy extends \Entity\City implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    private function _load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    
    public function getCuId()
    {
        $this->_load();
        return parent::getCuId();
    }

    public function setArea(\Entity\Area $area)
    {
        $this->_load();
        return parent::setArea($area);
    }

    public function getAreas()
    {
        $this->_load();
        return parent::getAreas();
    }

    public function getId()
    {
        $this->_load();
        return parent::getId();
    }

    public function setName($name)
    {
        $this->_load();
        return parent::setName($name);
    }

    public function getName()
    {
        $this->_load();
        return parent::getName();
    }

    public function setCode($code)
    {
        $this->_load();
        return parent::setCode($code);
    }

    public function getCode()
    {
        $this->_load();
        return parent::getCode();
    }

    public function setStatus($status)
    {
        $this->_load();
        return parent::setStatus($status);
    }

    public function getStatus()
    {
        $this->_load();
        return parent::getStatus();
    }

    public function setCreatedAt($created_at)
    {
        $this->_load();
        return parent::setCreatedAt($created_at);
    }

    public function getCreatedAt()
    {
        $this->_load();
        return parent::getCreatedAt();
    }

    public function setUpdatedAt($updated_at)
    {
        $this->_load();
        return parent::setUpdatedAt($updated_at);
    }

    public function getUpdatedAt()
    {
        $this->_load();
        return parent::getUpdatedAt();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'name', 'code', 'status', 'updated_at', 'created_at', 'areas', 'centralUnit');
    }
}