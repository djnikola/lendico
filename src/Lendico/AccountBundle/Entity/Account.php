<?php

namespace Lendico\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Account
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("name")
 */
class Account
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * 
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", columnDefinition="ENUM('active', 'inactive')") 
     */
    private $status;

    /**
     *
     * @ORM\OneToMany(targetEntity="AccountAmount", mappedBy="account", indexBy="currency")
     */
    private $amounts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->amounts = new ArrayCollection();
    }
    
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Account
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Returns true if account is active.
     * 
     * @return boolean
     */
    public function isActive()
    {
        return ($this->getStatus() == "active");
    }
    
    /**
     * Returns amounts for currency.
     * 
     * @return ArrayCollection
     */
    public function getAmounts()
    {
        return $this->amounts;
    }
    
    /**
     * Returns amount based on currency.
     * 
     * @param string $currency Currency
     * @return type
     * @throws InvalidArgumentException
     */
    public function getAmount($currency)
    {
        if (!isset($this->amounts[$currency])) {
            throw new InvalidArgumentException("No currency.");
        }

        return $this->amounts[$currency];
    }
    
    /**
     * Fill object with provided array.
     * 
     * @param array $params
     * @return boolean
     */
    public function fill(array $params = null)
    {
        if (!is_array($params) || count($params) === 0) {
            return false;
        }
        
        foreach ($params as $key => $value) 
        {
            if ( property_exists(__CLASS__, $key) ) {
                $this->$key = $value;
            }
        }
        
        return true;
    }
}

