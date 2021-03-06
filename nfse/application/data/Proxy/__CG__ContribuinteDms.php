<?php

namespace Proxy\__CG__\Contribuinte;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Dms extends \Contribuinte\Dms implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    /** @private */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();
        return parent::getId();
    }

    public function setId($id)
    {
        $this->__load();
        return parent::setId($id);
    }

    public function getCodigoPlanilha()
    {
        $this->__load();
        return parent::getCodigoPlanilha();
    }

    public function setCodigoPlanilha($codigo_planilha)
    {
        $this->__load();
        return parent::setCodigoPlanilha($codigo_planilha);
    }

    public function getIdUsuario()
    {
        $this->__load();
        return parent::getIdUsuario();
    }

    public function setIdUsuario($iIdUsuario)
    {
        $this->__load();
        return parent::setIdUsuario($iIdUsuario);
    }

    public function getIdContribuinte()
    {
        $this->__load();
        return parent::getIdContribuinte();
    }

    public function setIdContribuinte($iIdContribuinte)
    {
        $this->__load();
        return parent::setIdContribuinte($iIdContribuinte);
    }

    public function getDataOperacao()
    {
        $this->__load();
        return parent::getDataOperacao();
    }

    public function setDataOperacao($data_operacao)
    {
        $this->__load();
        return parent::setDataOperacao($data_operacao);
    }

    public function getAnoCompetencia()
    {
        $this->__load();
        return parent::getAnoCompetencia();
    }

    public function setAnoCompetencia($ano_comp)
    {
        $this->__load();
        return parent::setAnoCompetencia($ano_comp);
    }

    public function getMesCompetencia()
    {
        $this->__load();
        return parent::getMesCompetencia();
    }

    public function setMesCompetencia($mes_comp)
    {
        $this->__load();
        return parent::setMesCompetencia($mes_comp);
    }

    public function getStatus()
    {
        $this->__load();
        return parent::getStatus();
    }

    public function setStatus($status)
    {
        $this->__load();
        return parent::setStatus($status);
    }

    public function getOperacao()
    {
        $this->__load();
        return parent::getOperacao();
    }

    public function setOperacao($operacao)
    {
        $this->__load();
        return parent::setOperacao($operacao);
    }

    public function getDmsNotas()
    {
        $this->__load();
        return parent::getDmsNotas();
    }

    public function addDmsNotas(\Contribuinte\DmsNota $oDmsNota)
    {
        $this->__load();
        return parent::addDmsNotas($oDmsNota);
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'codigo_planilha', 'id_contribuinte', 'id_usuario', 'data_operacao', 'ano_comp', 'mes_comp', 'status', 'operacao', 'oDmsNotas');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields as $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}