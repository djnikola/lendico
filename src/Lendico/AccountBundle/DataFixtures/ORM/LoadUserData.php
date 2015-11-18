<?php
/**
 * Class that loads data into database.
 * Read README.md for details.
 * 
 * @author nikola
 */
namespace Lendico\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Lendico\AccountBundle\Entity\Account;
use Lendico\AccountBundle\Entity\AccountAmount;

class LoadAccountData implements FixtureInterface
{
    /**
     * Loads initial data into database.
     * 
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $from = new Account();
        $from->setName('from');
        $from->setStatus('active');
        $manager->persist($from);
        
        //adding dollar currency.
        $dollar_amount = new AccountAmount();
        $dollar_amount->setCurrency("USD");
        $dollar_amount->setAmount(100);
        $dollar_amount->setAccount($from);
        $manager->persist($dollar_amount);
        
        $franc_amount = new AccountAmount();
        $franc_amount->setCurrency("CHF");
        $franc_amount->setAmount(200);
        $franc_amount->setAccount($from);
        $manager->persist($franc_amount);
        
        
        $to = new Account();
        $to->setName('to');
        $to->setStatus('active');
        $manager->persist($to);
        
        $dollar_amount = new AccountAmount();
        $dollar_amount->setCurrency("USD");
        $dollar_amount->setAmount(1);
        $dollar_amount->setAccount($to);
        $manager->persist($dollar_amount);
        
        $chf_amount = new AccountAmount();
        $chf_amount->setCurrency("CHF");
        $chf_amount->setAmount(2);
        $chf_amount->setAccount($to);
        $manager->persist($chf_amount);
        
        $test = new Account();
        $test->setName('test-inactive');
        $test->setStatus('active');
        $manager->persist($test);

        $manager->flush();
    }

}