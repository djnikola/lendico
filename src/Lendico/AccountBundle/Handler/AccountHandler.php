<?php
/**
 * Implementation of Account interface.
 * 
 * @author nikola
 */
namespace Lendico\AccountBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Lendico\AccountBundle\Entity\Account;
use Lendico\AccountBundle\Entity\AccountAmount;
use Lendico\AccountBundle\Form\AccountType;
use Lendico\AccountBundle\Form\AccountAmountType;
use Lendico\AccountBundle\Exception\InvalidFormException;
use JMS\Serializer\SerializerBuilder;


class AccountHandler implements AccountHandlerInterface
{
    
    /**
     * Object manager
     *
     * @var ObjectManager 
     */
    private $om;
    
    /**
     * Account entity class.
     * 
     * @var Account 
     */
    private $accountEntityClass;
    
    /**
     * AccountAmount entity class.
     * 
     * @var AccountAmount 
     */
    private $accountAmountEntityClass;
    
    /**
     *
     * @var ObjectRepository
     */
    private $accountRepository;
    
    
    /**
     * Form Factory Interface class.
     *
     * @var FormFactoryInterface
     */
    private $formFactory;
    
    /**
     * Constructor of Account Handler object.
     * 
     * @param ObjectManager $om Object manager
     * @param string $accountEntityClass Name of entety class.
     * @param string $accountAmountEntityClass Name of entety class.
     * @param FormFactoryInterface $formFactory Form factory.
     */
    public function __construct(ObjectManager $om, $accountEntityClass, $accountAmountEntityClass,
        FormFactoryInterface $formFactory)
    {
        $this->om = $om;
        $this->accountEntityClass = $accountEntityClass;
        $this->accountAmountEntityClass = $accountAmountEntityClass;
        $this->accountRepository = $this->om->getRepository($this->accountEntityClass);
        $this->formFactory = $formFactory;
    }
    
    /**
     * Returns array of all acounts.
     * 
     * @return array Array of accounts
     */
    public function getAll()
    {
        return $this->accountRepository->findAll();
    }
    
    /**
     * Return account based on account id.
     * 
     * @param int $id Account id
     * @return Account
     */
    public function getAccount($id)
    {
        return $this->accountRepository->find($id);
    }
    
    /**
     * Create a new Account.
     *
     * @param array $parameters
     * @return AccountInterface
     */
    public function createAccount(array $parameters)
    {
        $account = new $this->accountEntityClass();
        return $this->processNewAccountForm($account, $parameters, 'POST');
    }
    
    /**
     * Deactivating Account.
     * 
     * @param Account $account Account to be deactiveted. 
     * @return AccountTypeInteface
     */
    public function deactiveAccount(Account $account)
    {
        $account->setStatus("inactive");
        $serializer = SerializerBuilder::create()->build();
        $parameters = json_decode( $serializer->serialize($account, 'json'), true );
        unset($parameters["id"]);
        unset($parameters["amounts"]);
        return $this->processNewAccountForm($account, $parameters, 'PUT');
    }
    
    /**
     * Calcuate amount based of $op. 
     * Currenctly avaiable are adding and sub. 
     * Default calculation is adding.
     * Adding:
     *  $a + $b
     * Sub:
     * $a - $b
     * 
     * @param string $op Enum values (+, -)
     * @param float $a Operand
     * @param string $b Operand
     * @return float Result.
     */
    private function amountCalc($op, $a, $b)
    {
        $c = 0;
        switch ($op) {
            
            case "-":
                $c = $a - $b;
                break;
            case "+":
            default:
                $c = $a + $b;
                break;
        }
        return $c;
    }
    
    /**
     * It calulcates new amount based on existing value, operator and new value.
     * 
     * $params["amount"] Amount 
     * $params["currency"] Currency of amount.
     * $params["op"] Operator to be used in calucaltions. Add or sub. 
     * 
     * @param Account $account Account
     * @param array $params params
     */
    public function calcAccount(Account $account, array $params)
    {
        if ( !$account->isActive() ) {
            throw new Exception("Account: $account->getId() is not active.", 400);
        }
        
        //get amount base on currency key.
        $account_amount = $account->getAmounts()->get($params["currency"]);
        
        //if amount does exists update.
        if ( $account_amount != null ) {
            $new_amount = $this->amountCalc($params["op"], $account_amount->getAmount(), $params["amount"]);
            $account_amount = $this->updateAmount(
                $account_amount,
                array(
                    "amount" => $new_amount,
                    "currency" => $params["currency"]
                ),
                'POST'
            );
        }
        else {
            // if amount does not exists create new.
            $account_amount = $this->setAmount(
                $account,
                array(
                    "amount" => $params["amount"],
                    "currency" => $params["currency"]
                )
            );
        }
    }
    
    /**
     * Transfer amount of money $from_account to $to_account.
     * 
     * @param Account $from_account Transfer form account.
     * @param Account $to_account Transfer to account.
     * @param array $params Array of params
     * @throws \Lendico\AccountBundle\Handler\Exception
     */
    public function transferAccount(Account $from_account, Account $to_account, array $params)
    {
        // suspend auto-commit
        $this->om->getConnection()->beginTransaction();
        try {
            $params["op"] = "-";
            $this->calcAccount($from_account, $params);
            $params["op"] = "+";
            $this->calcAccount($to_account, $params);

            // Try and commit the transaction
            $this->om->getConnection()->commit();
         } catch (Exception $e) {
            // Rollback the failed transaction attempt
            $this->om->getConnection()->rollback();
            throw $e;
         }
    }

    /**
     * Set amount for account.
     * 
     * @param Account $account
     * @param array $parameters
     * @return AccountTypeForm
     */
    private function setAmount(Account $account, array $parameters)
    {
        $account_amount = new $this->accountAmountEntityClass();
        $account_amount->setAccount($account);
        return $this->processNewAccountAmountForm($account_amount, $parameters, 'POST');
    }

    /**
     * Create new account if not exists or update existing.
     * 
     * @param Account $account Account entity class.
     * @param array $parameters Array of parameters
     * @param string $method method POST or PUT 
     * @return Account Retrurn Account
     * @throws InvalidFormException
     */
    private function processNewAccountForm(Account $account, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new AccountType(), $account, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);
        
        if ($form->isValid()) {
            $account->fill((array)$form->getData());
            $this->om->persist($account);
            $this->om->flush($account);

            return $account;
        }
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    /**
     * 
     * @param AccountAmount $account_amount Account amount to be updated.
     * @param array $parameters Array of params
     * @param string $method method.
     * @return account_amount
     */
    private function updateAmount(AccountAmount $account_amount, array $parameters, $method)
    {
        return $this->processNewAccountAmountForm($account_amount, $parameters, $method);
    }
    
    /**
     * Processing of Account amount object.
     * 
     * @param AccountAmount $account_amount Account amount to be process 
     * @param array $parameters Array of params.
     * @param string $method Method
     * @return AccountAmount Account amount
     * @throws InvalidFormException
     */
    private function processNewAccountAmountForm(AccountAmount $account_amount, array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new AccountAmountType(), $account_amount, array('method' => $method));
        $form->submit($parameters, 'PATCH' !== $method);

        if ($form->isValid()) {
            $account_amount->fill((array)$form->getData());
            if ( 'PATCH' !== $method ) {
                $this->om->persist($account_amount);
            }
            else {
                $this->om->merge($account_amount);
            }
            $this->om->flush($account_amount);

            return $account_amount;
        }
        throw new InvalidFormException('Invalid submitted data', $form);
    }

    

}