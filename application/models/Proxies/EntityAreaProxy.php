<?php

namespace Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class EntityAreaProxy extends \Entity\Area implements \Doctrine\ORM\Proxy\Proxy
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

    
    public function getDashBoardReqests()
    {
        $this->_load();
        return parent::getDashBoardReqests();
    }

    public function getMonthTarget()
    {
        $this->_load();
        return parent::getMonthTarget();
    }

    public function getLastMonthTarget()
    {
        $this->_load();
        return parent::getLastMonthTarget();
    }

    public function getAvgProcessingDelay()
    {
        $this->_load();
        return parent::getAvgProcessingDelay();
    }

    public function getEmployees()
    {
        $this->_load();
        return parent::getEmployees();
    }

    public function getPlaceOrderIds()
    {
        $this->_load();
        return parent::getPlaceOrderIds();
    }

    public function getNetPlaceOrderIds()
    {
        $this->_load();
        return parent::getNetPlaceOrderIds();
    }

    public function getStoreBalance()
    {
        $this->_load();
        return parent::getStoreBalance();
    }

    public function getStoreCollection()
    {
        $this->_load();
        return parent::getStoreCollection();
    }

    public function getStoreTotalAmount()
    {
        $this->_load();
        return parent::getStoreTotalAmount();
    }

    public function getToDayPlaceOrderIds()
    {
        $this->_load();
        return parent::getToDayPlaceOrderIds();
    }

    public function getStoreWalletAmount()
    {
        $this->_load();
        return parent::getStoreWalletAmount();
    }

    public function getMonthPlaceOrderIds()
    {
        $this->_load();
        return parent::getMonthPlaceOrderIds();
    }

    public function getLastMonthPlaceOrderIds()
    {
        $this->_load();
        return parent::getLastMonthPlaceOrderIds();
    }

    public function getPickupBoys()
    {
        $this->_load();
        return parent::getPickupBoys();
    }

    public function getCustomers()
    {
        $this->_load();
        return parent::getCustomers();
    }

    public function getNewCustomers()
    {
        $this->_load();
        return parent::getNewCustomers();
    }

    public function setIsServiceTax($isServiceTax = 0)
    {
        $this->_load();
        return parent::setIsServiceTax($isServiceTax);
    }

    public function getIsServiceTax()
    {
        $this->_load();
        return parent::getIsServiceTax();
    }

    public function setCatalogId(\Entity\Catalog $catalog_id)
    {
        $this->_load();
        return parent::setCatalogId($catalog_id);
    }

    public function getCatalogId()
    {
        $this->_load();
        return parent::getCatalogId();
    }

    public function addService(\Entity\Service $service)
    {
        $this->_load();
        return parent::addService($service);
    }

    public function removeService()
    {
        $this->_load();
        return parent::removeService();
    }

    public function getServices()
    {
        $this->_load();
        return parent::getServices();
    }

    public function setApartment(\Entity\Apartment $apartment)
    {
        $this->_load();
        return parent::setApartment($apartment);
    }

    public function getApartments()
    {
        $this->_load();
        return parent::getApartments();
    }

    public function setCityId(\Entity\City $city_id = NULL)
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

    public function setAddress($address)
    {
        $this->_load();
        return parent::setAddress($address);
    }

    public function getAddress()
    {
        $this->_load();
        return parent::getAddress();
    }

    public function setLandmark($landmark)
    {
        $this->_load();
        return parent::setLandmark($landmark);
    }

    public function getLandmark()
    {
        $this->_load();
        return parent::getLandmark();
    }

    public function setPincode($pincode)
    {
        $this->_load();
        return parent::setPincode($pincode);
    }

    public function getPincode()
    {
        $this->_load();
        return parent::getPincode();
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
        return array('__isInitialized__', 'id', 'name', 'code', 'address', 'landmark', 'pincode', 'mobile', 'status', 'isServiceTax', 'avarageProcessingDelay', 'updated_at', 'created_at', 'apartments', 'customers', 'pickupBoys', 'placeOrderIds', 'employees', 'storeTargets', 'customerRequests', 'catalog_id', 'city_id', 'areaServices');
    }
}