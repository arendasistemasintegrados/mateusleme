<?php

namespace Proxy\__CG__\Administrativo;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Perfil extends \Administrativo\Perfil implements \Doctrine\ORM\Proxy\Proxy
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

    public function getNome()
    {
        $this->__load();
        return parent::getNome();
    }

    public function setNome($nome)
    {
        $this->__load();
        return parent::setNome($nome);
    }

    public function getAdministrativo()
    {
        $this->__load();
        return parent::getAdministrativo();
    }

    public function setAdministrativo($administrativo)
    {
        $this->__load();
        return parent::setAdministrativo($administrativo);
    }

    public function getAcoes()
    {
        $this->__load();
        return parent::getAcoes();
    }

    public function addAcao(\Administrativo\Acao $acao)
    {
        $this->__load();
        return parent::addAcao($acao);
    }

    public function delAcao(\Administrativo\Acao $acao)
    {
        $this->__load();
        return parent::delAcao($acao);
    }

    public function getPerfis()
    {
        $this->__load();
        return parent::getPerfis();
    }

    public function addPerfis(\Administrativo\Perfil $perfil)
    {
        $this->__load();
        return parent::addPerfis($perfil);
    }

    public function delPerfis(\Administrativo\Perfil $perfil)
    {
        $this->__load();
        return parent::delPerfis($perfil);
    }

    public function getTipo()
    {
        $this->__load();
        return parent::getTipo();
    }

    public function setTipo($iTipo)
    {
        $this->__load();
        return parent::setTipo($iTipo);
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'nome', 'administrativo', 'tipo', 'acoes', 'perfis');
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