<?php
/**
 * It handels work with account entety object.
 *
 * @author nikola
 */

namespace Lendico\AccountBundle\Handler;

use Lendico\AccountBundle\Entity\Account;

interface AccountHandlerInterface
{
    /**
     * Returns array of all acounts.
     * 
     * @return array Array of accounts
     */
    public function getAll();
    
    /**
     * Return account based on account id.
     * 
     * @param int $id Account id
     * @return Account
     */
    public function getAccount($id);
    
    /**
     *
     * @param array $parameters
     *
     * @return AccountInterface
     */
    public function createAccount(array $parameters);
    
    /**
     * Deactive account.
     * 
     * @param Account $account
     */
    public function deactiveAccount(Account $account);
    
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
    public function calcAccount(Account $account, array $params);
    
    /**
     * Transfer amount of money $from_account to $to_account.
     * 
     * @param Account $from_account Transfer form account.
     * @param Account $to_account Transfer to account.
     * @param array $params Array of params
     * @throws \Lendico\AccountBundle\Handler\Exception
     */
    public function transferAccount(Account $from_account, Account $to_account, array $params);
    
}
