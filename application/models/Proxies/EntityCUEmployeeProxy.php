<?php

namespace Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class EntityCUEmployeeProxy extends \Entity\CUEmployee implements \Doctrine\ORM\Proxy\Proxy
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

    
    public function setRoleId(\Entity\Role $role_id = NULL)
    {
        $this->_load();
        return parent::setRoleId($role_id);
    }

    public function getRoleId()
    {
        $this->_load();
        return parent::getRoleId();
    }

    public function setCuId(\Entity\CUnit $cu_id)
    {
        $this->_load();
        return parent::setCuId($cu_id);
    }

    public function getCuId()
    {
        $this->_load();
        return parent::getCuId();
    }

    public function setCityId(\Entity\Area $city_id = NULL)
    {
        $this->_load();
        return parent::setCityId($city_id);
    }

    public function getCityId()
    {
        $this->_load();
        return parent::getCityId();
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

    public function setEmail($email)
    {
        $this->_load();
        return parent::setEmail($email);
    }

    public function getEmail()
    {
        $this->_load();
        return parent::getEmail();
    }

    public function setMobile($mobile)
    {
        $this->_load();
        return parent::setMobile($mobile);
    }

    public function getMobile()
    {
        $this->_load();
        return parent::getMobile();
    }

    public function setPassword($password)
    {
        $this->_load();
        return parent::setPassword($password);
    }

    public function getPassword()
    {
        $this->_load();
        return parent::getPassword();
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


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'name', 'email', 'mobile', 'password', 'password_salt', 'status', 'updated_at', 'created_at', 'role_id', 'city_id', 'cu_id');
    }
}