<?php

namespace Lendico\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AccountAmount
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"account", "currency"},
 *     errorPath="currency",
 *     message="This currency already exists for that account."
 * 
 * )
 * 
 */
class AccountAmount
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
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * 
     */
    private $account;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", columnDefinition="ENUM('USD', 'EUR', 'CHF')") 
     */
    private $currency;


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
     * Set amount
     *
     * @param float $amount
     *
     * @return AccountAmount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return AccountAmount
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    /**
     * Get account
     * 
     * @return Account
     */
    function getAccount()
    {
        return $this->account;
    }

    /**
     * Set account
     * 
     * @param Account $account
     * @return AccountAmount $account
     */
    function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
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

