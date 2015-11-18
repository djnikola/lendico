<?php

namespace Lendico\AccountBundle\Controller;
use FOS\RestBundle\Controller\Annotations\View;
use Lendico\AccountBundle\Entity\Account;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lendico\AccountBundle\Exception\InvalidFormException;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;

class AccountsController extends FOSRestController
{
    
    /**
     * 
     * Get all accounts
     * 
     * @ApiDoc(
     *  resource = true,
     *  description = "Get all accounts with all currencies.",
     *  statusCodes = {
     *     200 = "Returned when successful."
     *  }
     *      
     * )
     * 
     * 
     * @return array array of accounts
     * @View()
     */
    public function getAccountsAction()
    {
        $accounts = $this->container->get('lendico_account.handler')->getAll();
        return array('accounts' => $accounts);
        
    }
    
    /**
     * Get Account by account id.
     * 
     * @ApiDoc(
     *  resource = true,
     *  description = "Get account by account id.",
     *  statusCodes = {
     *     200 = "Returned when successful.",
     *     404 = "Returned when the account is not found."
     *  }
     *      
     * )
     * 
     * @param int $id account id.
     * @View()
     * 
     */
    public function getAccountAction($id)
    {
        $account = $this->getAccount($id);
        return $account;
    }
    
    /**
     * Creates new account.
     * 
     * @ApiDoc(
     *  resource = true,
     *  description = "Creates a new account from the submitted data.",
     *  statusCodes = {
     *     201 = "Account created. Returned when successful.",
     *     400 = "Returned when the form has errors."
     *  }
     *      
     * )
     * 
     * @param Request $request
     * @View()
     * @return type
     */
    public function postAccountAction(Request $request)
    {
        try {

            $account = $this->container->get('lendico_account.handler')->createAccount(
                $request->request->all()
            );
            $routeOptions = array(
                'id' => $account->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('get_accounts', $routeOptions, Codes::HTTP_CREATED);
        } catch (InvalidFormException $exception) {
            return $exception->getForm()->getErrors();
        }
    }
    
    /**
     * Deactive account by account id.
     * 
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "Returned when successful update.",
     *     404 = "Returned when the form has errors."
     *   }
     * )
     * 
     * @param Request $request
     * @param int $id account id
     * @return type
     */
    public function putAccountAction(Request $request, $id)
    {
        try {
            
            $account = $this->getAccount($id);
            
            if (!$account) {
                $statusCode = Codes::HTTP_NOT_FOUND;
            } else {
                $statusCode = Codes::HTTP_NO_CONTENT;
                $account = $this->container->get('lendico_account.handler')->deactiveAccount(
                    $account
                );
            }
            $routeOptions = array(
                'id' => $account->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('get_accounts', $routeOptions, $statusCode);

        } catch (InvalidFormException $exception) {

            return $exception->getForm();
        }
    }
    
    /**
     * Adding deposit amount or widrowing amount from account.
     * Account must be ative.
     * 
     * @ApiDoc(
     *   resource = true,
     *   description = "Operates with account ($account_id) and amount($amount) with currency($currency). Account must be active.
     *      Avaible operators are add and sub ",
     *   statusCodes = {
     *     201 = "Returned when the AccountAmount is created.",
     *     204 = "Returned when successful.",
     *   }
     * )
     * 
     * 
     * @param Request $request
     * @param int $account_id Account id.
     * @param float $amount Amount for account.
     * @param string $currency Currency enum values("USD", "EUR", "CHF")
     * @param string $oper Operations +|-
     * @return type
     */
    public function putAccountAmountCurrencyOperAction(Request $request, $account_id, $amount, $currency, $oper)
    {
        
        /** $account @var Account */
        $account = $this->getAccount($account_id);

        $routeOptions = array(
            'id' => $account->getId(),
            '_format' => $request->get('_format')
        );
        
        //if account is not active
        if ( !$account->isActive() ) {
            throw new NotFoundHttpException("Account with id ". $account_id . " is not active.");
        }
        
        $this->container->get('lendico_account.handler')->calcAccount(
            $account, 
            array(
                "amount" => $amount,
                "currency" => $currency,
                "op" => $oper
            )
        );

        return $this->routeRedirectView('get_accounts', $routeOptions, Codes::HTTP_OK);
        
    }

    /**
     * transfering amount form first account to second.
     * Both accounts must be active.
     * 
     * @ApiDoc(
     *   description = "Trnasfering money from account ($account_id), amount($amount) and currency($currency) to account with id 
     * $to_account_id with to. Both accounts must be active.",
     *   statusCodes = {
     *     201 = "Returned when the AccountAmount is created.",
     *     204 = "Returned when successful.",
     *   }
     * )
     * 
     * @param Request $request
     * @param int $from_account_id Account id from which we transfer money.
     * @param float $from_amount Amount of mony that we are trnafering.
     * @param currency $from_currency Currency of money that we are transfering.
     * @param int $to_account_id Account id where we are transfering money.
     * @return type
     */
    public function putAccountAmountCurrencyToAction(Request $request, 
        $from_account_id, $from_amount, $from_currency, $to_account_id)
    {
        /** $account @var Account */
        $from_account = $this->getAccount($from_account_id);

        $routeOptions = array(
            'id' => $from_account->getId(),
            '_format' => $request->get('_format')
        );
        
        //if account is not active.
        if ( !$from_account->isActive() ) {
            throw new NotFoundHttpException("Account with id ". $from_account_id . " is not active.");
        }
        
        /** $account @var Account */
        $to_account = $this->getAccount($to_account_id);
        
        
        //if account is not active.
        if ( !$to_account->isActive() ) {
            throw new NotFoundHttpException("Account with id ". $to_account . " is not active.");
        }
        
        $this->container->get('lendico_account.handler')->transferAccount(
            $from_account,
            $to_account,
            array(
                "amount" => $from_amount,
                "currency" => $from_currency
            )
        );
        
        return $this->routeRedirectView('get_accounts', $routeOptions, Codes::HTTP_OK);
    }

    /**
     * Returns account from account hadler or throw exception.
     * 
     * @param int $id Account id
     * @return Account Account
     * @throws NotFoundHttpException
     */
    protected function getAccount($id)
    {
        $account = $this->container->get('lendico_account.handler')->getAccount($id);
        if ( !$account ) {
            throw new NotFoundHttpException(sprintf('The account with account id  \'%s\' was not found.',$id));
        }
        return $account;
    }
}